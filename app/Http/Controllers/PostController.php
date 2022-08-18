<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return response([

            'posts' => Post::orderby('created_at', 'desc')->with('user:id,name')->withcount('comments')->first()
        ],200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:50',
            'body' => 'required|string|max:255'
        ]);

        $post = Post::create([
            'body' => $data['body'],
            'title' => $data['title'],
            'user_id' => auth()->user()->id
        ]);

        return response([
            'message' => 'Post Created.',
            'post' => $post
        ], 200);
    }

    public function show($id)
    {
        return response([
            'post' => Post::where('id', $id)->withcount('comments')->first()
        ],200);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if(!$post)
        {
            return response([
                'message' => 'Post not found'
            ],403);
        }

        if($post->user_id != auth()->user()->id)
        {
            return response([
                'message' => 'Denied'
            ],403);
        }

        $data = $request->validate([
            'body' => 'required|string|max:255',
            'title' => 'required|string|max:50'
        ]);

        $post->update([
            'body' => $data['body'],
            'title' => $data['title']

        ]);

        return response([
            'message' => 'Post updated.',
            'post' => $post
        ], 200);
    }

    public function destroy(Post $post)
    {
        $post = Post::find($post);

        if(!$post)
        {
            return response([
                'message' => 'Post not found'
            ],403);
        }

        if($post->user_id != auth()->user()->id)
        {
            return response([
                'message' => 'Denied'
            ],403);
        }

        $post->comments()->delete();
        $post->delete();

        return response([
            'message' => 'post deleted'
        ],200);
    }

}
