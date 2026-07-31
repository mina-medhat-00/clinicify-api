<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class postController extends Controller
{
    public function post(Request $request)
    {
        $user_id = Auth::user()->id;
        $all_data = ($request->input('data'));
        $content = $all_data['content'];
        if(isset($all_data['postImg'])){
            if($all_data['postImg']){
                $imageData =  $all_data['postImg'];
                $image = str_replace('data:image/jpeg;base64,', '', $imageData);
                $image2 = str_replace(' ', '+', $image);
                $decodedImage = base64_decode($image2);
                $imagePath = 'images/' . time() . '_' . uniqid() . '.jpg'; // Assuming the image is in PNG format
                Storage::disk('public')->put($imagePath, $decodedImage);
            }
        }
        $post = Post::create([
            'user_id' => $user_id,
            'content' => $content,
            'post_img' =>isset($imagePath)?$imagePath:null
        ]);

        $state = "good, ok";
        $message = "your data added successfully";
        $data = [
            'post_id' => $post->id
        ];
        return response(compact('state', 'message', 'data'), 200);
    }

    public function comment(Request $request)
    {
        $user_id = Auth::user()->id;
        $all_data = ($request->input('data'));
        $content = $all_data['content'];
        $post_id = $all_data['postId'];
        $comment_id=null;
        if(isset($all_data['commentId'])){
            $comment_id=$all_data['commentId'];
            $comment = Comment::create([
                'user_id' => $user_id,
                'content' => $content,
                'post_id' => $post_id
            ]);
            $reply = Reply::create([
                'post_id' => $post_id,
                'reply_on'=>$comment_id,
                'reply_id'=>$comment->id
            ]);

            $state = "good, ok";
            $message = "your data added successfully";
            $data = [
                'comment_id' => $reply->id
            ];
            return response(compact('state', 'message', 'data'), 200);
        }
        $comment = Comment::create([
            'user_id' => $user_id,
            'content' => $content,
            'post_id' => $post_id
        ]);
        $state = "good, ok";
        $message = "your data added successfully";
        $data = [
            'comment_id' => $comment->id
        ];
        return response(compact('state', 'message', 'data'), 200);
    }
    public function get_posts(){
        $data = array();
        $all_posts=Post::all();
        if($all_posts) {
            foreach ($all_posts as $post) {
                $user = \App\Models\User::where([['id', '=', $post->user_id]])->first();
                $like_emoji = Like::where([['post_id', '=', $post->id],['like_type', '=', "like"],['is_post','=',1]])->count();
                $dislike = Like::where([['post_id', '=', $post->id],['like_type', '=', "dislike"],['is_post','=',1]])->count();
                $angry = Like::where([['post_id', '=', $post->id],['like_type', '=', "angry"],['is_post','=',1]])->count();
               $no_comments=Comment::where([['post_id', '=', $post->id]])->count();
                $res = [
                    'post_id' => $post->id,
                    'user_id' => $post->user_id,
                    'content' => $post->content,
                    'issued_time' => $post->created_at,
                    'like_emoji' => $like_emoji,
                    'dislike' => $dislike,
                    'angry' => $angry,
                    'post_img' => $post->post_img?asset('storage/' . $post->post_img):null,
                    'nick_name' => $user->name,
                    'img_url' => asset('storage/' . $user->img_url),
                    'num_comments'=>$no_comments
                ];
                array_push($data, $res);
            }
        }
        $state="good, ok";
        $message="your data added successfully";
        return response(compact('state', 'message', 'data'), 200);
    }

    public function get_comments()
    {
        if(isset($_GET['postId'])) {
            $post_id=($_GET['postId']);
            $all_comments=Comment::where([['post_id', '=', $post_id]])->get();
            $all_replies=Reply::where([['post_id', '=', $post_id]])->get();
            $state="good, ok";
            $message="your data added successfully";
            $data = array();

            foreach ($all_comments as $comment){
                $user=\App\Models\User::where([['id', '=', $comment->user_id]])->first();
                $like_emoji = Like::where([['comment_id', '=', $comment->id],['like_type', '=', "like"]])->count();
                $dislike = Like::where([['comment_id', '=', $comment->id],['like_type', '=', "dislike"]])->count();
                $angry = Like::where([['comment_id', '=', $comment->id],['like_type', '=', "angry"]])->count();
                $is_reply=Reply::where([['reply_id', '=', $comment->id]])->first();
                $num_replies=Reply::where('reply_on','=',$comment->id)->count();
                $res=[
                    'reply_on'=> null,
                    'comment_id'=> $comment->id,
                    'post_id'=> $comment->post_id,
                    'user_id'=> $comment->user_id,
                    'content'=> $comment->content,
                    'issued_time'=> $comment->created_at,
                    'num_replies'=> $num_replies,
                    'like_emoji' => $like_emoji,
                    'dislike' => $dislike,
                    'angry' => $angry,
                    'nick_name'=>$user->name,
                    'num_comments'=>26,

                    'img_url' => asset('storage/' . $user->img_url),
                ];
                if(empty($is_reply)) {
                    array_push($data, $res);
                }
            }
            foreach ($all_replies as $rep){
                $comm=Comment::where([['id', '=', $rep->reply_id]])->first();
                $user=\App\Models\User::where([['id', '=', $comm->user_id]])->first();
                $num_replies=Reply::where('reply_on','=',$comment->id)->count();

                $res=[
                    'reply_on'=> $rep->reply_on,
                    'comment_id'=> $rep->reply_id,
                    'post_id'=> $comm->post_id,
                    'user_id'=> $comm->user_id,
                    'content'=> $comm->content,
                    'issued_time'=> $comm->created_at,
                    'num_replies'=> $num_replies,
                    'like_emoji'=> $comm->like_emoji,
                    'dislike'=> $comm->dislike,
                    'angry'=> $comm->angry,
                    'nick_name'=>$user->name,
                    'img_url' => asset('storage/' . $user->img_url),
                ];
                array_push($data, $res);
            }

            return response(compact('state', 'message', 'data'), 200);
        }

    }

    }
