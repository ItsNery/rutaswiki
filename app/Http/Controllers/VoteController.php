<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TransitRoute;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function store(Request $request, TransitRoute $route)
    {
        $validated = $request->validate([
            'value' => 'required|integer|in:-1,1',
        ]);

        $userId = Auth::id();
        $newValue = $validated['value'];

        DB::transaction(function () use ($route, $userId, $newValue) {
            $existingVote = Vote::where('transit_route_id', $route->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingVote) {
                if ($existingVote->value == $newValue) {
                    $existingVote->delete();
                } else {
                    $existingVote->update(['value' => $newValue]);
                }
            } else {
                Vote::create([
                    'transit_route_id' => $route->id,
                    'user_id' => $userId,
                    'value' => $newValue,
                ]);
            }

            $score = Vote::where('transit_route_id', $route->id)->sum('value');
            $route->update(['vote_score' => $score]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'vote_score' => $route->fresh()->vote_score,
            ]);
        }

        return back();
    }
}
