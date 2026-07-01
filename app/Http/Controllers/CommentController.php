<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TransitRoute;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, TransitRoute $route)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $route->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }
}
