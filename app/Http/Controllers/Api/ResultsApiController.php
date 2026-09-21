<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Results;
use Illuminate\Support\Facades\Validator;

class ResultsApiController extends Controller
{
    /**
     * Get all results (paginated)
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $results = Results::orderBy('result_date', 'desc')
                ->orderBy('result_time', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * Get latest result
     */
    public function latest()
    {
        try {
            $result = Results::orderBy('result_date', 'desc')
                ->orderBy('result_time', 'desc')
                ->first();

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'No results found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * Get results by date
     */
    public function byDate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD'], 400);
            }

            $results = Results::whereDate('result_date', $request->date)
                ->orderBy('result_time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }
}
