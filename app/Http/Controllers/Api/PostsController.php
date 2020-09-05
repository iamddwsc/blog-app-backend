<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function create(Request $request){

        $post = new Post();
        $post->user_id = Auth::user()->id;
        $post->desc = $request->desc;

        //check if post has photo
        if($request->photo != ''){
            //choose a unique name for photo
            $photo = time().'jpg';
            //need link storage folder to publish
            file_put_contents('storage/posts/'.$photo,base64_decode($request->photo));
            $post->photo = $photo;
        }
        $post->save();
        $post->user;
        return response()->json([
            'success' => true,
            'message' => 'posted',
            'post' => $post
        ]);
    }


    public function update(Request $request){
        $post = Post::find($request->id);
        //check if user is editing his own post
        if(Auth::user()->id != $request->id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
        }
        $post->desc = $request->desc;
        $post->update();
        return response()->json([
            'success' => true,
            'message' => 'post edited'
        ]);
    }

    public function delete(Request $request){
        $post = Post::find($request->id);
        //check if user is editing his own post
        if(Auth::user()->id != $request->id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
        }

        //check if post has photo
        if($request->photo != ''){
            Storage::delete('public/posts/'.$post->photo);
        }
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'post deleted'
        ]);
    }

    public function posts(){
        $posts = Post::orderBy('id','desc')->get();
        foreach ($posts as $post) {
            //get users of post
            $post->user;
            //comment count
            $post['commentsCount'] = count($post->comments);
            //likes count
            $post['likesCount'] = count($post->likes);
            //check if user liked his own post
            $post['selfLike'] = false;
            foreach ($post->likes as $like) {
                if($like->user_id == Auth::user()->id){
                    $post['selflike'] = true;
                }
            }
        }
        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }
}
