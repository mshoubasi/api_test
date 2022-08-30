<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CLike;

class CLikeController extends Controller
{
    public function likeOrUnlikeComment($id)
    {
        $comment = Comment::find($id);

        if(!$comment)
        {
            return response([
                'message' => 'comment not found.'
            ], 403);
        }

        $like = $comment->clikes()->where('user_id', auth()->user()->id)->first();

        // if not liked then like
        if(!$like)
        {
            CLike::create([
                'comment_id' => $id,
                'user_id' => auth()->user()->id
            ]);

            return response([
                'message' => 'Liked'
            ], 200);
        }
        // else dislike it
        $like->delete();

        return response([
            'message' => 'Disliked'
        ], 200);
    }
}
