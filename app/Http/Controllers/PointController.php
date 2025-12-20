<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PointHistory;

class PointController extends Controller
{
    public function getUserPoints(Request $request)
    {
        $userId = $request->query('user_id'); // Or via Auth::id() if authenticated
        // Fallback checks
        if (!$userId)
            return response()->json(['poin' => 0]);

        // Use Query Builder for consistency with Webhook and reliability with String IDs
        $poin = DB::table('users')
            ->where('user_id', $userId)
            ->value('poin');

        return response()->json(['poin' => (int) $poin]);
    }

    public function getPointHistory(Request $request)
    {
        $userId = $request->query('user_id'); 
        // Fallback checks
        if (!$userId) return response()->json(['data' => []]);

        $history = PointHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
