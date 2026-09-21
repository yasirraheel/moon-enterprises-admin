# Avatar Handling Guide

## Storage Location
- **Directory**: `public/avatar/`
- **Full Path**: `C:\xampp\htdocs\prize-bond-booking-system\public\avatar\`

## Database Storage
- **Column**: `users.avatar`
- **Value**: Only filename (e.g., `1234567890_1.jpg`)
- **NOT**: Full path or URL

## URL Generation
### Correct Way:
```php
// In Blade templates
{{ asset('avatar/' . $user->avatar) }}

// In API responses
'avatar' => $user->avatar ? asset('avatar/' . $user->avatar) : null
```

### Examples:
- Database value: `1234567890_1.jpg`
- Generated URL: `http://yoursite.com/avatar/1234567890_1.jpg`

## File Upload Process
1. **Upload Location**: `public_path('avatar')` = `public/avatar/`
2. **Filename Format**: `time() . '_' . $user->id . '.' . $extension`
3. **Example**: `1705123456_1.jpg`

## Common Mistakes to Avoid
❌ **Wrong**:
```php
url('public/img', $user->avatar)  // Wrong directory
asset('public/avatar/' . $user->avatar)  // Double public
$user->avatar  // Just filename, not URL
```

✅ **Correct**:
```php
asset('avatar/' . $user->avatar)  // Correct
```

## Testing Avatar URLs
1. Check if file exists: `public/avatar/filename.jpg`
2. Test URL: `http://yoursite.com/avatar/filename.jpg`
3. Check browser console for 404 errors

## Debugging
- Add `onerror` handler to images
- Log avatar filenames in views
- Check file permissions on `public/avatar/` directory
