<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\ApkVersion;
use App\Models\Countries;
use App\Models\ManualNotification;
use App\Models\FcmNotificationSetting;
use App\Models\Transaction;
use App\Helper;
use App\Services\TransactionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * User Login
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ], [
                'username.required' => 'Username is required',
                'username.string' => 'Username must be a valid text',
                'password.required' => 'Password is required',
                'password.string' => 'Password must be a valid text',
                'password.min' => 'Password must be at least 6 characters',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Clean username if it looks like a phone number
            $username = $request->username;
            if (strpos($username, '+92') === 0) {
                $username = substr($username, 3); // Remove +92 prefix
            } else if (strpos($username, '92') === 0 && strlen($username) === 13) {
                $username = substr($username, 2); // Remove 92 prefix
            }

            $credentials = [
                'username' => $username,
                'password' => $request->password
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Check user status
                if ($user->status == 'suspended') {
                    Auth::logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'Account has been suspended'
                    ], 403);
                }

                if ($user->status == 'pending') {
                    Auth::logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'Account not confirmed. Please contact support.'
                    ], 403);
                }

                // Generate API token
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'username' => $user->username,
                        'phone' => $user->phone,
                        'city' => $user->city,
                        'avatar' => $user->avatar ? asset('avatar/' . $user->avatar) : null,
                        'balance' => $user->balance ?? 0,
                        'status' => $user->status,
                        'dealership_id' => $user->dealership_id,
                        'dealer_status' => $user->dealer_status,
                        'dealer_commission' => $user->dealer_commission,
                        'commission_earned' => $user->commission_earned,
                        'created_at' => $user->date,
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number or password'
                ], 401);
            }

        } catch (\Exception $e) {
            \Log::error('Login API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * User Registration
     */
    public function register(Request $request)
    {
        try {
            $settings = AdminSettings::first();

            // Check if registration is active
            if ($settings->registration_active != '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is currently disabled'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|regex:/^03[0-9]{9}$/',
                'city' => 'required|string|max:100',
                'password' => 'required|min:8|confirmed',
            ], [
                'full_name.required' => 'Full name is required',
                'full_name.string' => 'Full name must be a valid text',
                'full_name.max' => 'Full name cannot exceed 255 characters',
                'phone.required' => 'Phone number is required',
                'phone.string' => 'Phone number must be a valid text',
                'phone.unique' => 'This phone number is already registered',
                'phone.regex' => 'Phone number must be a valid Pakistan mobile number (e.g., 03001234567)',
                'city.required' => 'City is required',
                'city.string' => 'City must be a valid text',
                'city.max' => 'City cannot exceed 100 characters',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Format phone number
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (strpos($phone, '92') === 0) {
                $phone = substr($phone, 2);
            }

            $formattedPhone = '+92' . $phone;

            // Use phone number without country code as username
            $username = $phone; // Phone without +92 prefix

            // Check if phone or username already exists (Custom Unique Check)
            if (User::where('phone', $formattedPhone)->orWhere('username', $username)->exists()) {
                 return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'phone' => ['This phone number is already registered']
                    ]
                ], 422);
            }

            // Get user country
            $country = Countries::whereCountryCode(Helper::userCountry())->first();

            // Determine status based on email verification setting
            $status = $settings->email_verification == '1' ? 'pending' : 'active';
            $confirmation_code = $settings->email_verification == '1' ? Str::random(100) : '';

            // Create user
            $user = User::create([
                'username' => $username,
                'full_name' => $request->full_name,
                'phone' => $formattedPhone,
                'city' => $request->city,
                'password' => Hash::make($request->password),
                'avatar' => $settings->avatar,
                'cover' => $settings->cover,
                'status' => $status,
                'account_no' => '',
                'activation_code' => $confirmation_code,
                'oauth_uid' => '',
                'oauth_provider' => '',
                'token' => Str::random(75),
                'ip' => $request->ip(),
                'balance' => $settings->signup_bonus_credits ?? 0
            ]);

            // Generate API token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'avatar' => $user->avatar ? asset('avatar/' . $user->avatar) : null,
                    'balance' => $user->balance ?? 0,
                    'status' => $user->status,
                    'dealership_id' => $user->dealership_id,
                    'dealer_status' => $user->dealer_status,
                    'dealer_commission' => $user->dealer_commission,
                    'commission_earned' => $user->commission_earned,
                    'created_at' => $user->date,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            \Log::error('Logout API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'avatar' => $user->avatar ? asset('avatar/' . $user->avatar) : null,
                    'balance' => $user->balance ?? 0,
                    'status' => $user->status,
                    'dealership_id' => $user->dealership_id,
                    'dealer_status' => $user->dealer_status,
                    'dealer_commission' => $user->dealer_commission,
                    'commission_earned' => $user->commission_earned,
                    'created_at' => $user->date
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get App Settings
     */
    public function settings()
    {
        try {
            $settings = AdminSettings::first();

            return response()->json([
                'success' => true,
                'data' => [
                    'app_name' => $settings->title,
                    'app_logo' => $settings->app_logo ? url('public/img', $settings->app_logo) : null,
                    'app_logo_light' => $settings->logo_light ? url('public/img', $settings->logo_light) : null,
                    'app_tagline' => $settings->tagline ?? 'Prize Bond Booking System',
                    'primary_color' => $settings->color_default,
                    'registration_active' => $settings->registration_active == '1',
                    'email_verification' => $settings->email_verification == '1',
                    'signup_bonus_credits' => $settings->signup_bonus_credits ?? 100,
                    'facebook_login' => $settings->facebook_login == 'on',
                    'google_login' => $settings->google_login == 'on',
                    'twitter_login' => $settings->twitter_login == 'on',
                    'captcha_enabled' => $settings->captcha == 'on',
                    'theme' => $settings->theme ?? 'light',
                    'currency_symbol' => $settings->currency_symbol ?? '$',
                    'currency_code' => $settings->currency_code ?? 'USD',
                    'currency_position' => $settings->currency_position ?? 'before',
                    'whatsapp_number' => $settings->whatsapp_number ?? null,
                    'whatsapp_group_link' => $settings->whatsapp_group_link ?? null
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Settings API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get Manual Notifications (Public + User-specific)
     */
    public function getNotifications(Request $request)
    {
        try {
            $userId = auth()->id();


            // Get both public and user-specific notifications
            $notifications = NotificationService::getUserNotifications($userId, 50);


            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'action_type' => $notification->action_type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'image' => $notification->image_url,
                    'metadata' => $notification->metadata,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null
                ];
            });


            return response()->json([
                'success' => true,
                'data' => $formattedNotifications,
                'count' => $formattedNotifications->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Notifications API Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get Notification Count (Public + User-specific)
     */
    public function getNotificationCount(Request $request)
    {
        try {
            $userId = auth()->id();
            $count = NotificationService::getUserNotificationCount($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Notification Count API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        try {
            $userId = auth()->id();


            // Find the notification
            $notification = \App\Models\ManualNotification::where('id', $notificationId)
                ->where('is_active', true)
                ->where(function ($query) use ($userId) {
                    $query->where('type', 'public')
                          ->orWhere(function ($q) use ($userId) {
                              $q->where('type', 'user_specific')
                                ->where('user_id', $userId);
                          });
                })
                ->first();

            if (!$notification) {

                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found or access denied'
                ], 404);
            }

            // Update the read status based on notification type
            if ($notification->type === 'public') {
                // For public notifications, insert into user_read_public_notifications table
                \DB::table('user_read_public_notifications')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'notification_id' => $notificationId
                    ],
                    [
                        'read_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $readAt = now();
            } else {
                // For user-specific notifications, update the read_at field directly
                $notification->update(['read_at' => now()]);
                $readAt = $notification->read_at;
            }


            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Mark Notification as Read API Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'notification_id' => $notificationId,
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        try {
            $userId = auth()->id();

            // 1. Mark all user-specific notifications as read
            \App\Models\ManualNotification::where('user_id', $userId)
                ->where('type', 'user_specific')
                ->where('is_active', true)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // 2. Mark all public notifications as read
            // Get all active public notifications
            $publicNotifications = \App\Models\ManualNotification::where('type', 'public')
                ->where('is_active', true)
                ->pluck('id');

            // Get IDs of public notifications already read by user
            $readPublicNotificationIds = \DB::table('user_read_public_notifications')
                ->where('user_id', $userId)
                ->whereIn('notification_id', $publicNotifications)
                ->pluck('notification_id');

            // Find IDs that need to be marked as read
            $unreadPublicNotificationIds = $publicNotifications->diff($readPublicNotificationIds);

            // Bulk insert into user_read_public_notifications
            $dataToInsert = [];
            $now = now();
            foreach ($unreadPublicNotificationIds as $id) {
                $dataToInsert[] = [
                    'user_id' => $userId,
                    'notification_id' => $id,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            if (!empty($dataToInsert)) {
                \DB::table('user_read_public_notifications')->insert($dataToInsert);
            }

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Mark All Notifications Read API Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get Game Categories
     */
    public function getGameCategories()
    {
        try {
            // Fetch all categories (with optional related data if you want)
            $categories = \App\Models\Categories::where('mode', 'on')->orderBy('id', 'asc')->get();

            // Map data simply
            $data = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->thumbnail
                        ? url('public/img-category/' . $category->thumbnail)
                        : null,
                    'status' => $category->mode ?? 'active',
                    'date' => $category->date,
                    'time' => $category->time,
                    'created_at' => $category->date ? \Carbon\Carbon::parse($category->date)->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Subcategories by Category ID
     */
    public function getSubcategories(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');

            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category ID is required'
                ], 400);
            }

            // Check if category exists and is active
            $category = \App\Models\Categories::where('id', $categoryId)
                ->where('mode', 'on')
                ->first();
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found or inactive'
                ], 404);
            }

            // Fetch subcategories for the given category
            $subcategories = \App\Models\Subcategories::where('category_id', $categoryId)
                ->where('mode', 'on')
                ->orderBy('id', 'asc')
                ->get();


            // Map data
            $data = $subcategories->map(function ($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'category_id' => $subcategory->category_id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'mode' => $subcategory->mode,
                    'description' => $subcategory->description,
                    'keywords' => $subcategory->keywords,
                    'start_date' => $subcategory->start_date,
                    'start_time' => $subcategory->start_time,
                    'close_date' => $subcategory->close_date,
                    'close_time' => $subcategory->close_time,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Subcategories Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading subcategories',
                'data' => []
            ], 500);
        }
    }

    /**
     * Update User Profile
     */
    public function updateProfile(Request $request)
    {
        try {

            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'full_name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'city' => 'sometimes|string|max:100',
                'password' => 'sometimes|string|min:6',
                'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update user fields
            if ($request->has('full_name')) {
                $user->full_name = $request->full_name;
            }
            if ($request->has('phone')) {
                $user->phone = $request->phone;
            }
            if ($request->has('city')) {
                $user->city = $request->city;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                $avatarPath = public_path('avatar');

                // Create avatar directory if it doesn't exist
                if (!file_exists($avatarPath)) {
                    mkdir($avatarPath, 0755, true);
                }

                // Delete old avatar if exists
                if ($user->avatar) {
                    $oldAvatarPath = $avatarPath . '/' . $user->avatar;
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                // Move uploaded file to avatar directory
                try {
                    $moved = $file->move($avatarPath, $filename);

                    $user->avatar = $filename;
                } catch (\Exception $e) {
                    \Log::error('File move failed: ' . $e->getMessage());
                    throw $e;
                }
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'avatar' => $user->avatar ? asset('avatar/' . $user->avatar) : null,
                    'balance' => $user->balance ?? 0,
                    'status' => $user->status,
                    'dealer_status' => $user->dealer_status,
                    'dealer_commission' => $user->dealer_commission,
                    'commission_earned' => $user->commission_earned,
                    'created_at' => $user->date
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Profile API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Create Order (Protected API)
     */
    public function createOrder(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Get admin settings for bond amount validation
            $settings = AdminSettings::first();

            $result = $this->processSingleOrder($user, $request->all(), $settings);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null
                ], $result['status'] ?? 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $result['data']
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Create Order API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Create Bulk Orders (Protected API)
     */
    public function createBulkOrder(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'orders' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = AdminSettings::first();
            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($request->orders as $index => $orderData) {
                // Ensure user_phone is passed if not in order data but in user profile
                if (!isset($orderData['user_phone'])) {
                    $orderData['user_phone'] = $user->phone;
                }

                $result = $this->processSingleOrder($user, $orderData, $settings);

                if ($result['success']) {
                    $successCount++;
                    $results[] = [
                        'index' => $index,
                        'success' => true,
                        'data' => $result['data']
                    ];
                } else {
                    $failCount++;
                    $results[] = [
                        'index' => $index,
                        'success' => false,
                        'message' => $result['message'],
                        'errors' => $result['errors'] ?? null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Processed $successCount orders successfully. Failed: $failCount.",
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Create Bulk Order API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Process a single order
     */
    private function processSingleOrder($user, $data, $settings)
    {
        // Validate request data
        $validator = Validator::make($data, [
            'game_name'  => 'required|string|max:255',
            'bond_name'  => 'required|string|max:255',
            'rttp'       => 'required|string|max:255',
            'first'      => 'nullable|numeric|min:0',
            'second'     => 'nullable|numeric|min:0',
            'user_phone' => 'nullable|string|max:20',
        ]);

        $validator->after(function ($validator) use ($data, $settings) {
            $first = $data['first'] ?? null;
            $second = $data['second'] ?? null;

            // Check if either first or second is provided
            if (is_null($first) && is_null($second)) {
                $validator->errors()->add('first', 'Either first or second field is required.');
                $validator->errors()->add('second', 'Either first or second field is required.');
            }

            // Validate bond amounts against admin settings
            if ($settings && ($settings->min_bond_amount > 0 || $settings->max_bond_amount > 0)) {
                $firstAmount = (float) $first;
                $secondAmount = (float) $second;

                // Validate first amount
                if ($first !== null && $first > 0) {
                    if ($settings->min_bond_amount > 0 && $firstAmount < $settings->min_bond_amount) {
                        $validator->errors()->add('first', 'First amount must be at least ' . $settings->min_bond_amount);
                    }
                    if ($settings->max_bond_amount > 0 && $firstAmount > $settings->max_bond_amount) {
                        $validator->errors()->add('first', 'First amount cannot exceed ' . $settings->max_bond_amount);
                    }
                }

                // Validate second amount
                if ($second !== null && $second > 0) {
                    if ($settings->min_bond_amount > 0 && $secondAmount < $settings->min_bond_amount) {
                        $validator->errors()->add('second', 'Second amount must be at least ' . $settings->min_bond_amount);
                    }
                    if ($settings->max_bond_amount > 0 && $secondAmount > $settings->max_bond_amount) {
                        $validator->errors()->add('second', 'Second amount cannot exceed ' . $settings->max_bond_amount);
                    }
                }
            }
        });

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        // Dynamic RTTP Pattern Validation
        $activePrompts = \App\Models\GamePrompts::where('status', 'active')->get();
        if ($activePrompts->count() > 0) {
            $isRttpValid = false;
            $matchedPrompt = null;
            $matchedStart = null;
            $matchedEnd = null;

            foreach ($activePrompts as $prompt) {
                // Skip if start or end is missing (allow "0")
                if ($prompt->number_start === null || $prompt->number_start === '' || 
                    $prompt->number_end === null || $prompt->number_end === '') {
                    continue;
                }

                // Remove the prompt name from the start/end strings to isolate the pattern
                // Case-insensitive removal of the prompt name
                $cleanStart = trim(str_ireplace($prompt->prompt, '', $prompt->number_start));
                $cleanEnd = trim(str_ireplace($prompt->prompt, '', $prompt->number_end));
                
                // If stripping resulted in empty string but original was not empty, 
                // it implies the prompt name WAS the pattern (e.g. prompt "0", start "0").
                // In this case, use the original value or "0" if it was "0".
                // Actually, if cleanStart is empty, parsePromptNumber returns 0, which is correct for "0".
                // But let's be safe: if cleanStart is empty and original was numeric, maybe keep it?
                // The test script showed "0" -> "" -> parsePromptNumber -> 0 works.
                // But if the pattern was "00", strip "0" -> "", lost padding.
                
                // Fix: Only strip if the prompt name is NOT numeric, to avoid stripping "0" from "0" or "1" from "10".
                // Or better: Only strip if the result is not empty?
                
                // Let's refine the stripping logic based on user feedback "open is pattren name" (text).
                // If the prompt name is numeric, we should be careful.
                // But the user said "0 pattren". Maybe the prompt name IS "0".
                // If I don't strip "0", then CleanStart="0". Works.
                // If I DO strip "0", CleanStart="". Works for value 0.
                
                // The main issue was empty() check. Let's fix that first.
                // And also, if cleanStart is empty, let's ensure we don't lose padding info if possible.
                // But standard "0" -> "0" works.
                
                $start = $this->parsePromptNumber($cleanStart);
                $end = $this->parsePromptNumber($cleanEnd);
                
                // Create regex pattern from start structure
                // Escape special characters in prefix and suffix
                $prefix = preg_quote($start['prefix'], '/');
                $suffix = preg_quote($start['suffix'], '/');
                
                // Regex to capture the number part
                if (preg_match('/^' . $prefix . '(.*)' . $suffix . '$/', $data['rttp'], $matches)) {
                    $numberPart = $matches[1];
                    
                    // Check if the captured part is numeric
                    if (is_numeric($numberPart)) {
                        $number = (int)$numberPart;
                        
                        // Check if number is within range
                        if ($number >= $start['number'] && $number <= $end['number']) {
                            // Check format (padding)
                            // Reconstruct the expected string to verify format match (e.g. "05" vs "5")
                            $expectedRttp = $start['prefix'] . str_pad($number, $start['padding'], '0', STR_PAD_LEFT) . $start['suffix'];
                            
                            if ($data['rttp'] === $expectedRttp) {
                                $isRttpValid = true;
                                $matchedPrompt = $prompt;
                                $matchedStart = $start;
                                $matchedEnd = $end;
                                break;
                            }
                        }
                    }
                }
            }

            if (!$isRttpValid) {
                return [
                    'success' => false,
                    'message' => 'Invalid RTTP pattern. The provided RTTP does not match any active game prompt patterns.',
                    'status' => 422
                ];
            }
        }

        // Enforce prompt limits (first/second) if a matching prompt was found
        if (isset($matchedPrompt) && ($matchedPrompt->first_limit !== null || $matchedPrompt->second_limit !== null)) {
            $firstLimit = $matchedPrompt->first_limit;
            $secondLimit = $matchedPrompt->second_limit;

            // Limit check is per RTTP (not the whole prompt range)
            $firstSum = (float) \App\Models\Orders::where('game_name', $data['game_name'])
                ->where('rttp', $data['rttp'])
                ->where('status', '!=', 'rejected')
                ->sum('first');

            $secondSum = (float) \App\Models\Orders::where('game_name', $data['game_name'])
                ->where('rttp', $data['rttp'])
                ->where('status', '!=', 'rejected')
                ->sum('second');

            $projectedFirst = $firstSum + (float) ($data['first'] ?? 0);
            $projectedSecond = $secondSum + (float) ($data['second'] ?? 0);

            if ($firstLimit !== null && $projectedFirst > $firstLimit) {
                return [
                    'success' => false,
                    'message' => 'Limit reached for this prompt (first).',
                    'status' => 400
                ];
            }

            if ($secondLimit !== null && $projectedSecond > $secondLimit) {
                return [
                    'success' => false,
                    'message' => 'Limit reached for this prompt (second).',
                    'status' => 400
                ];
            }
        }

        // Check close time
        $category = \App\Models\Categories::where('name', $data['game_name'])->first();
        if ($category) {
            $subcategory = \App\Models\Subcategories::where('name', $data['bond_name'])
                ->where('category_id', $category->id)
                ->first();

            if ($subcategory) {
                 // Check if close date and time are set
                 if (!empty($subcategory->close_date) && !empty($subcategory->close_time)) {
                     try {
                         $closeDateTime = \Carbon\Carbon::parse($subcategory->close_date . ' ' . $subcategory->close_time);

                         if (now()->greaterThan($closeDateTime)) {
                             return [
                                 'success' => false,
                                 'message' => 'Order placement is closed for this game. Closed at: ' . $closeDateTime->format('Y-m-d H:i:s'),
                                 'status' => 400
                             ];
                         }
                     } catch (\Exception $e) {
                         \Log::error('Date parsing error in processSingleOrder: ' . $e->getMessage());
                     }
                 }
            }
        }

        // Calculate total amount (first + second)
        $firstAmount = (float) ($data['first'] ?? 0);
        $secondAmount = (float) ($data['second'] ?? 0);
        $totalAmount = $firstAmount + $secondAmount;

        // Dealer Commission Calculation
        $commissionAmount = 0;
        $payableAmount = $totalAmount;
        $isDealer = $user->dealer_status === 'approved';

        if ($isDealer && $user->dealer_commission > 0) {
            // Calculate commission: (Total * Commission%) / 100
            $commissionAmount = ($totalAmount * $user->dealer_commission) / 100;
            // Dealer pays less: Total - Commission
            $payableAmount = $totalAmount - $commissionAmount;
        }

        // Check if user has sufficient balance (checking against payable amount)
        $user->refresh();
        if ($user->balance < $payableAmount) {
            return [
                'success' => false,
                'message' => 'Insufficient balance. Required: ' . $payableAmount . ', Available: ' . $user->balance,
                'status' => 400
            ];
        }

        // Create order
        $order = \App\Models\Orders::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'user_phone' => $data['user_phone'] ?? $user->phone ?? '',
            'game_name' => $data['game_name'],
            'bond_name' => $data['bond_name'],
            'rttp' => $data['rttp'],
            'first' => $data['first'] ?? null,
            'second' => $data['second'] ?? null,
            'status' => 'OK',
            'commission_amount' => $commissionAmount
        ]);

        // Update User's earned commission stats if commission was applied
        if ($commissionAmount > 0) {
            $user->increment('commission_earned', $commissionAmount);
        }

        // Log transaction and update user balance
        TransactionService::logOrder(
            $user->id,
            $totalAmount, // Log FULL amount
            $order->id,
            $data['game_name'],
            $isDealer
                ? 'Order placed (Dealer): ' . $data['game_name'] . ' - RTTP: ' . $data['rttp'] . ' (Comm: ' . $commissionAmount . ')'
                : 'Order placed: ' . $data['game_name'] . ' - RTTP: ' . $data['rttp'],
            $commissionAmount
        );

        // If commission exists, credit it back immediately
        if ($commissionAmount > 0) {
            TransactionService::logCommission(
                $user->id,
                $commissionAmount,
                $order->id,
                $data['game_name']
            );
        }

        // Format status for display
        $statusDisplay = $order->status;
        if ($order->status == 'first_win') {
            $statusDisplay = 'First Win';
        } elseif ($order->status == 'second_win') {
            $statusDisplay = 'Second Win';
        }

        return [
            'success' => true,
            'data' => [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'username' => $order->username,
                'user_phone' => $order->user_phone,
                'game_name' => $order->game_name,
                'bond_name' => $order->bond_name,
                'rttp' => $order->rttp,
                'first' => $order->first,
                'second' => $order->second,
                'total_amount' => $totalAmount,
                'status' => $statusDisplay,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'balance_after' => $user->fresh()->balance
            ]
        ];
    }

    /**
     * Parse prompt number string to extract prefix, number, suffix and padding
     */
    private function parsePromptNumber($str)
    {
        if (preg_match('/^(\D*)(\d+)(\D*)$/', $str, $matches)) {
            return [
                'prefix' => $matches[1],
                'number' => (int)$matches[2],
                'suffix' => $matches[3],
                'padding' => strlen($matches[2])
            ];
        }
        return [
            'prefix' => '',
            'number' => (int)$str,
            'suffix' => '',
            'padding' => strlen($str)
        ];
    }


    /**
     * Get User Orders (Protected API)
     */
    public function getUserOrders(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Get query parameters
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            $status = $request->input('status'); // Optional filter by status
            $getAll = $request->input('get_all', false); // New parameter to get all orders

            // Build query
            $query = \App\Models\Orders::where('user_id', $user->id)
                ->orderBy('created_at', 'DESC');

            // Filter by status if provided
            if ($status && in_array($status, ['pending', 'approved', 'rejected', 'OK'])) {
                $query->where('status', $status);
            }

            // Get results - either all or paginated
            if ($getAll) {
                $orders = $query->get();
                $totalOrders = $orders->count();
            } else {
                // Limit per_page to prevent abuse, but allow up to 1000
                $perPage = min($perPage, 1000);
                $orders = $query->paginate($perPage, ['*'], 'page', $page);
                $totalOrders = $orders->total();
            }

            // Format response data
            $data = $orders->map(function ($order) {
                // Format status for display
                $statusDisplay = $order->status;
                if ($order->status == 'first_win') {
                    $statusDisplay = 'First Win';
                } elseif ($order->status == 'second_win') {
                    $statusDisplay = 'Second Win';
                }

                return [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'username' => $order->username,
                    'user_phone' => $order->user_phone,
                    'game_name' => $order->game_name,
                    'bond_name' => $order->bond_name,
                    'rttp' => $order->rttp,
                    'first' => $order->first,
                    'second' => $order->second,
                    'status' => $statusDisplay,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s')
                ];
            });

            $response = [
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $data,
                'total' => $totalOrders
            ];

            // Add pagination info only if not getting all orders
            if (!$getAll) {
                $response['pagination'] = [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                    'has_more_pages' => $orders->hasMorePages()
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Get User Orders API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get all active payment methods
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        try {
            $paymentMethods = \App\Models\PaymentMethod::active()
                ->ordered()
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'bank_or_account_name' => $method->bank_or_account_name,
                        'account_title' => $method->account_title,
                        'account_no' => $method->account_no,
                        'bank_image' => $method->bank_image ? url('public/img', $method->bank_image) : null,
                        'is_active' => $method->is_active,
                        'sort_order' => $method->sort_order,
                        'minimum_limit' => $method->minimum_limit,
                        'maximum_limit' => $method->maximum_limit,
                        'created_at' => $method->created_at,
                        'updated_at' => $method->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentMethods,
                'message' => 'Payment methods retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Payment Methods API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get all active withdrawal methods
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdrawalMethods()
    {
        try {
            $withdrawalMethods = \App\Models\WithdrawalMethod::active()
                ->ordered()
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'image' => $method->image ? url('public/img', $method->image) : null,
                        'min_amount' => $method->min_amount,
                        'max_amount' => $method->max_amount,
                        'is_active' => $method->is_active,
                        'created_at' => $method->created_at,
                        'updated_at' => $method->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $withdrawalMethods,
                'message' => 'Withdrawal methods retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Withdrawal Methods API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Create a new deposit request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDeposit(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'payment_method_id' => 'required|exists:payment_methods,id',
                'amount' => 'required|numeric|min:1',
                'transaction_id' => 'required|string|max:255',
                'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120'
            ]);

            // Handle payment proof upload
            $paymentProof = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProof = $this->handlePaymentProofUpload($request->file('payment_proof'));
            }

            // Create deposit record
            $deposit = \App\Models\Deposits::create([
                'user_id' => auth()->id(),
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'transaction_id' => $request->transaction_id,
                'payment_proof' => $paymentProof,
                'status' => 'pending'
            ]);

            // Notify User via App (API Notification)
            try {
                \App\Services\NotificationService::notifyDepositRequested(auth()->id(), $request->amount, $deposit->id);
            } catch (\Exception $e) {
                \Log::error('Error notifyDepositRequested API - ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                    'payment_method_id' => $deposit->payment_method_id,
                    'amount' => $deposit->amount,
                    'transaction_id' => $deposit->transaction_id,
                    'payment_proof' => $deposit->payment_proof,
                    'status' => $deposit->status,
                    'date' => $deposit->date
                ],
                'message' => 'Deposit request submitted successfully. It will be reviewed by admin.'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create Deposit API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit deposit request. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle payment proof file upload
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null
     * @throws \Exception
     */
    private function handlePaymentProofUpload($file)
    {
        try {
            $temp = 'public/temp/';
            $path = 'public/deposits/';

            if (!\File::exists($temp)) {
                \File::makeDirectory($temp, 0755, true);
            }
            if (!\File::exists($path)) {
                \File::makeDirectory($path, 0755, true);
            }

            $extension = $file->getClientOriginalExtension();
            $fileName = 'deposit-proof-' . time() . '-' . uniqid() . '.' . $extension;

            if ($file->move($temp, $fileName)) {
                if (\File::copy($temp . $fileName, $path . $fileName)) {
                    \File::delete($temp . $fileName);
                    return $fileName;
                } else {
                    \File::delete($temp . $fileName);
                    throw new \Exception('Failed to save payment proof');
                }
            } else {
                throw new \Exception('Failed to upload payment proof');
            }
        } catch (\Exception $e) {
            \Log::error('Payment proof upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user's deposit requests
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDeposits(Request $request)
    {
        try {
            $userId = auth()->id();

            // Get query parameters for filtering and pagination
            $status = $request->query('status'); // pending, approved, rejected
            $perPage = $request->query('per_page', 15);
            $perPage = min($perPage, 50); // Limit max per page to 50

            // Build query
            $query = \App\Models\Deposits::with(['paymentMethod'])
                ->where('user_id', $userId)
                ->orderBy('date', 'desc');

            // Apply status filter if provided
            if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $status);
            }

            // Get paginated results
            $deposits = $query->paginate($perPage);

            // Transform the data
            $transformedDeposits = $deposits->map(function ($deposit) {
                return [
                    'id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                    'amount' => $deposit->amount,
                    'transaction_id' => $deposit->transaction_id,
                    'payment_proof' => $deposit->payment_proof ? url('public/deposits', $deposit->payment_proof) : null,
                    'status' => $deposit->status,
                    'admin_notes' => $deposit->admin_notes,
                    'date' => $deposit->date,
                    'payment_method' => $deposit->paymentMethod ? [
                        'id' => $deposit->paymentMethod->id,
                        'bank_or_account_name' => $deposit->paymentMethod->bank_or_account_name,
                        'account_title' => $deposit->paymentMethod->account_title,
                        'account_no' => $deposit->paymentMethod->account_no,
                        'bank_image' => $deposit->paymentMethod->bank_image ? url('public/img', $deposit->paymentMethod->bank_image) : null,
                        'minimum_limit' => $deposit->paymentMethod->minimum_limit,
                        'maximum_limit' => $deposit->paymentMethod->maximum_limit
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedDeposits,
                'pagination' => [
                    'current_page' => $deposits->currentPage(),
                    'last_page' => $deposits->lastPage(),
                    'per_page' => $deposits->perPage(),
                    'total' => $deposits->total(),
                    'from' => $deposits->firstItem(),
                    'to' => $deposits->lastItem(),
                    'has_more_pages' => $deposits->hasMorePages()
                ],
                'message' => 'User deposits retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get User Deposits API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Create a new withdrawal request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createWithdrawal(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Validate the request
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'account_number' => 'required|string|max:255',
                'account_title' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255'
            ]);

            // Check if user has sufficient balance
            if ($user->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Required: ' . $request->amount . ', Available: ' . $user->balance
                ], 400);
            }

            // Use database transaction to ensure atomicity
            \DB::beginTransaction();
            try {
                // Create withdrawal record
                $withdrawal = \App\Models\Withdrawals::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'account_number' => $request->account_number,
                    'account_title' => $request->account_title,
                    'bank_name' => $request->bank_name,
                    'status' => 'pending'
                ]);

                // Instantly deduct amount from user balance and log transaction
                \App\Services\TransactionService::logWithdrawalRequest(
                    $user->id,
                    $request->amount,
                    $withdrawal->id,
                    'Withdrawal request created - Amount reserved'
                );

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Withdrawal Creation Transaction Error: ' . $e->getMessage());
                throw $e;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $withdrawal->id,
                    'user_id' => $withdrawal->user_id,
                    'amount' => $withdrawal->amount,
                    'account_number' => $withdrawal->account_number,
                    'account_title' => $withdrawal->account_title,
                    'bank_name' => $withdrawal->bank_name,
                    'status' => $withdrawal->status,
                    'date' => $withdrawal->date
                ],
                'message' => 'Withdrawal request submitted successfully. It will be reviewed by admin.'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create Withdrawal API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit withdrawal request. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user's withdrawal requests
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserWithdrawals(Request $request)
    {
        try {
            $userId = auth()->id();

            // Get query parameters for filtering and pagination
            $status = $request->query('status'); // pending, approved, rejected
            $perPage = $request->query('per_page', 15);
            $perPage = min($perPage, 50); // Limit max per page to 50

            // Build query
            $query = \App\Models\Withdrawals::where('user_id', $userId)
                ->orderBy('date', 'desc');

            // Apply status filter if provided
            if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $status);
            }

            // Get paginated results
            $withdrawals = $query->paginate($perPage);

            // Transform the data
            $transformedWithdrawals = $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'user_id' => $withdrawal->user_id,
                    'amount' => $withdrawal->amount,
                    'account_number' => $withdrawal->account_number,
                    'account_title' => $withdrawal->account_title,
                    'bank_name' => $withdrawal->bank_name,
                    'status' => $withdrawal->status,
                    'transaction_id' => $withdrawal->transaction_id,
                    'payment_proof' => $withdrawal->payment_proof ? asset('withdrawals/' . $withdrawal->payment_proof) : null,
                    'admin_notes' => $withdrawal->admin_notes,
                    'date' => $withdrawal->date
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedWithdrawals,
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                    'from' => $withdrawals->firstItem(),
                    'to' => $withdrawals->lastItem(),
                    'has_more_pages' => $withdrawals->hasMorePages()
                ],
                'message' => 'User withdrawals retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get User Withdrawals API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get Paid Services
     */
    public function getPaidServices(Request $request)
    {
        try {
            $paidServices = \App\Models\PaidService::active()->ordered()->get();

            // Get authenticated user using the same method as other protected routes
            $user = $request->user();
            $isAuthenticated = $user !== null;
            $userId = $user ? $user->id : null;


            // Get user's purchased services if authenticated
            $userPurchases = [];
            if ($isAuthenticated && $userId) {
                $userPurchases = \App\Models\PaidServiceSale::where('user_id', $userId)
                    ->where('status', 'active')
                    ->pluck('service_id')
                    ->toArray();

            }

            $services = $paidServices->map(function ($service) use ($userPurchases, $isAuthenticated) {
                $hasPurchased = in_array($service->id, $userPurchases);


                $serviceData = [
                    'id' => $service->id,
                    'title' => $service->title,
                    'price' => number_format($service->price, 2),
                    'description' => $service->description,
                    'image' => $service->image ? url('public/img', $service->image) : null,
                    'is_active' => $service->is_active,
                    'has_purchased' => $hasPurchased,
                    'show_buy_button' => !$hasPurchased
                ];

                // Only include golden_text if user has purchased
                if ($hasPurchased) {
                    $serviceData['golden_text'] = $service->golden_text;
                }

                return $serviceData;
            });

            return response()->json([
                'success' => true,
                'data' => $services,
                'debug' => [
                    'is_authenticated' => $isAuthenticated,
                    'user_id' => $userId,
                    'purchased_services' => $userPurchases
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Paid Services API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch paid services',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Test Authentication for Paid Services
     */
    public function testPaidServicesAuth(Request $request)
    {
        try {
            // Use the same authentication method as other protected routes
            $user = $request->user();
            $isAuthenticated = $user !== null;
            $userId = $user ? $user->id : null;

            if (!$isAuthenticated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated',
                    'debug' => [
                        'is_authenticated' => false,
                        'user_id' => null,
                        'user_object' => 'No user found'
                    ]
                ], 401);
            }

            // Get user's purchases
            $userPurchases = \App\Models\PaidServiceSale::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['service'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Authentication working',
                'debug' => [
                    'is_authenticated' => true,
                    'user_id' => $userId,
                    'user_username' => auth()->user()->username,
                    'total_purchases' => $userPurchases->count(),
                    'purchases' => $userPurchases->map(function($purchase) {
                        return [
                            'id' => $purchase->id,
                            'service_id' => $purchase->service_id,
                            'service_title' => $purchase->service->title,
                            'amount' => $purchase->amount,
                            'status' => $purchase->status,
                            'purchased_at' => $purchase->purchased_at
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Test Paid Services Auth Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Is Trial Setting
     */
    public function getIsTrial()
    {
        $setting = FcmNotificationSetting::where('notification_type', 'is_trial')->first();
        return response()->json([
            'is_trial' => $setting ? (bool)$setting->is_enabled : false
        ]);
    }

    /**
     * Purchase Paid Service
     */
    public function purchasePaidService(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'required|exists:paid_services,id'
            ]);

            $user = $request->user();
            $service = \App\Models\PaidService::findOrFail($request->service_id);

            // Check if service is active
            if (!$service->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This service is not available'
                ], 400);
            }

            // Check if user already purchased this service
            $existingPurchase = \App\Models\PaidServiceSale::where('user_id', $user->id)
                ->where('service_id', $service->id)
                ->where('status', 'active')
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already purchased this service'
                ], 400);
            }

            // Check if user has enough balance
            if ($user->balance < $service->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. You need Rs. ' . number_format($service->price, 2)
                ], 400);
            }

            // Create purchase record first
            $purchase = \App\Models\PaidServiceSale::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'amount' => $service->price,
                'status' => 'active',
                'purchased_at' => now()
            ]);

            // Log transaction
            TransactionService::logPaidService(
                $user->id,
                $service->price,
                $purchase->id,
                $service->title,
                'Paid service purchased: ' . $service->title
            );


            return response()->json([
                'success' => true,
                'message' => 'Service purchased successfully',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'remaining_balance' => $user->balance
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Purchase Paid Service API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to purchase service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get User Transactions
     */
    public function getUserTransactions(Request $request)
    {
        try {
            $userId = auth()->id();

            // Get query parameters for filtering and pagination
            $type = $request->query('type'); // 'credit' or 'debit'
            $transactionType = $request->query('transaction_type'); // 'deposit', 'withdrawal', etc.
            $perPage = $request->query('per_page', 15);
            $perPage = min($perPage, 50); // Limit max per page to 50

            // Build query
            $query = Transaction::where('user_id', $userId)
                ->orderBy('created_at', 'desc');

            // Apply type filter if provided
            if ($type && in_array($type, ['credit', 'debit'])) {
                $query->where('type', $type);
            }

            // Apply transaction type filter if provided
            if ($transactionType) {
                $query->where('transaction_type', $transactionType);
            }

            // Get paginated results
            $transactions = $query->paginate($perPage);

            // Transform the data to match admin panel structure
            $transformedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'type' => $transaction->type,
                    'transaction_type' => $transaction->transaction_type,
                    'transaction_type_label' => $transaction->transaction_type_label,
                    'current_balance' => $transaction->current_balance,
                    'amount' => $transaction->transaction_amount,
                    'remaining_balance' => $transaction->remaining_balance,
                    'description' => $transaction->description,
                    'reference_id' => $transaction->reference_id,
                    'reference_type' => $transaction->reference_type,
                    'metadata' => $transaction->metadata,
                    'created_at' => $transaction->created_at,
                    'formatted_current_balance' => $transaction->formatted_current_balance,
                    'formatted_amount' => $transaction->formatted_amount,
                    'formatted_remaining_balance' => $transaction->formatted_remaining_balance,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'has_more_pages' => $transactions->hasMorePages()
                ],
                'message' => 'User transactions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get User Transactions API Error: ' . $e->getMessage());
            \Log::error('Get User Transactions API Error Details: ', [
                'user_id' => auth()->id(),
                'request_params' => $request->all(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get APK Version Info - Public endpoint for Android app
     */
    public function getApkVersion()
    {
        try {
            // Get the active APK version
            $apkVersion = \App\Models\ApkVersion::getActiveVersion();

            if (!$apkVersion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active APK version found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'version_name' => $apkVersion->version_name,
                    'version_code' => $apkVersion->version_code,
                    'download_link' => $apkVersion->public_download_link,
                    'release_notes' => $apkVersion->release_notes,
                    'is_force_update' => $apkVersion->is_force_update,
                    'download_count' => $apkVersion->download_count ?? 0
                ],
                'message' => 'APK version retrieved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Get APK Version API Error: ' . $e->getMessage());
            \Log::error('Get APK Version API Error Details: ', [
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Increment APK Download Count - Public endpoint
     */
    public function incrementApkDownload()
    {
        try {
            // Get the active APK version
            $apkVersion = \App\Models\ApkVersion::getActiveVersion();

            if (!$apkVersion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active APK version found'
                ], 404);
            }

            // Increment the download count
            $apkVersion->incrementDownloadCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'download_count' => $apkVersion->fresh()->download_count
                ],
                'message' => 'Download count incremented successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Increment APK Download API Error: ' . $e->getMessage());
            \Log::error('Increment APK Download API Error Details: ', [
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Apply for dealership
     */
    public function applyForDealership(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->dealer_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already a dealer'
                ], 400);
            }

            if ($user->dealer_status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending dealership request'
                ], 400);
            }

            $user->dealer_status = 'pending';
            $user->save();

            // SEND FCM NOTIFICATION - SUBMITTED
            try {
                // Send API Notification (ManualNotification) which also triggers FCM via NotificationService
                \App\Services\NotificationService::notifyDealershipRequested($user->id);

                // No need to manually send FCM here anymore as NotificationService handles it
            } catch (\Exception $e) {
                \Log::error('Notification Dealership Submitted Error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Dealership application submitted successfully. Please wait for admin approval.',
                'data' => [
                    'status' => $user->dealer_status,
                    'date' => now()
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Apply for Dealership API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application: ' . $e->getMessage()
            ], 500);
        }
    }
}
