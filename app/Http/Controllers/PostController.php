<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return response([

            'posts' => Post::orderby('created_at', 'desc')->with('user:id,name')->withcount('comments', 'likes')->with('likes', function($like)
            {
                return $like->where('user_id', auth()->user()->id)
                    ->select('id', 'user_id', 'post_id')->get();
            })
            ->get()
        ],200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'body' => 'required|string|max:255',
            'media' => 'nullable'
        ]);



        $post = Post::create([
            'body' => $request->body,
            'title' => $request->title,
            'user_id' => auth()->user()->id
        ]);

        if ($request->hasFile('media')) {
             $post->addMultipleMediaFromRequest(['media'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('media');
                });
        }

            return response([
                'message' => 'Post Created.',
                'post' => $post,
            ]);

    }

    public function show($id)
    {
        return response([
            'post' => Post::where('id', $id)->withcount('comments', 'likes')->first()
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

         $request->validate([
            'body' => 'required|string|max:255',
            'title' => 'required|string|max:50',
            ]);

        $post->update([
            'body' => $request->body,
            'title' => $request->title,
        ]);

        if ($request->hasFile('media')) {
            $post->clearMediaCollection();
            $post->addMultipleMediaFromRequest(['media'])
               ->each(function ($fileAdder) {
                   $fileAdder->toMediaCollection('media');
               });
       }

        return response([
            'message' => 'Post updated.',
            'post' => $post
        ], 200);
    }

    public function destroy($id)
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


          $post->comments()->delete();
          $post->likes()->delete();
          $post->delete();

        return response([
            'message' => 'post deleted'
        ],200);
    }

}
