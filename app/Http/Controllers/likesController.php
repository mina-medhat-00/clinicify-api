<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class likesController extends Controller
{
    public function add_like(Request $request){
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user_id =Auth::user()->id;
        $all_data = ($request->input('data'));
        $likeType = isset($all_data['likeType'])?$all_data['likeType']:"like";
        $postId = $all_data['postId'];
        $isPost=isset($all_data['isPost'])?$all_data['isPost']:false;

        if($isPost) {
            $isPost = $all_data['isPost'];
            $like_emoji = Like::where([['post_id', '=', $postId], ['like_type', '=', "like"], ['is_post', '=', 1]])->count();
            $dislike = Like::where([['post_id', '=', $postId], ['like_type', '=', "dislike"], ['is_post', '=', 1]])->count();
            $angry = Like::where([['post_id', '=', $postId], ['like_type', '=', "angry"], ['is_post', '=', 1]])->count();
            $before_like = Like::where([['post_id', '=', $postId], ['is_post', '=', 1],['user_id','=',$user_id]])->first();

            if (!empty($before_like)) {
                if ($before_like->like_type == $likeType) {
                    $before_like->delete();
                    $like_emoji = ($likeType == "like") ? $like_emoji - 1 : $like_emoji;
                    $dislike = ($likeType == "dislike") ? $dislike - 1 : $dislike;
                    $angry = ($likeType == "angry") ? $angry - 1 : $angry;
                    //return response(compact('before_like'), 200);
                    $data = [
                        'like_id' => null,
                        'post_id' => $postId,
                        'comment_id' => null,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => null,
                        'like_emoji' => $like_emoji,
                        'dislike' => $dislike,
                        'angry' => $angry,
                    ];


                } else {
                    $like_emoji = ($before_like->like_type == "like") ? $like_emoji - 1 : $like_emoji;
                    $dislike = ($before_like->like_type == "dislike") ? $dislike - 1 : $dislike;
                    $angry = ($before_like->like_type == "angry") ? $angry - 1 : $angry;
                    $before_like->delete();
                    $like = Like::create([
                        'post_id' => $postId,
                        'comment_id' => null,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => $likeType
                    ]);
                    $like_emoji = ($likeType == "like") ? $like_emoji + 1 : $like_emoji;
                    $dislike = ($likeType == "dislike") ? $dislike + 1 : $dislike;
                    $angry = ($likeType == "angry") ? $angry + 1 : $angry;
                    $data = [
                        'like_id' => $like->id,
                        'post_id' => $postId,
                        'comment_id' => null,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => $likeType,
                        'like_emoji' => $like_emoji,
                        'dislike' => $dislike,
                        'angry' => $angry,
                    ];
                }
                $state = "good, ok";
                $message = "your data added successfully";

                return response(compact('state', 'message', 'data'), 200);

            }
            else{
                $like = Like::create([
                    'post_id' => $postId,
                    'comment_id' => null,
                    'user_id' => $user_id,
                    'is_post' => $isPost,
                    'like_type' => $likeType
                ]);
                $like_emoji = ($likeType == "like") ? $like_emoji + 1 : $like_emoji;
                $dislike = ($likeType == "dislike") ? $dislike + 1 : $dislike;
                $angry = ($likeType == "angry") ? $angry + 1 : $angry;
                $state = "good, ok";
                $message = "your data added successfully";
                $data = [
                    'like_id' => $like->id,
                    'post_id' => $postId,
                    'comment_id' => null,
                    'user_id' => $user_id,
                    'is_post' => $isPost,
                    'like_type' => $likeType,
                    'like_emoji' => $like_emoji,
                    'dislike' => $dislike,
                    'angry' => $angry,
                ];
                return response(compact('state', 'message', 'data'), 200);
            }
        }
        $commentId=null;
        if(isset($all_data['commentId'])) {
            $commentId=$all_data['commentId'];
            $like_emoji = Like::where([['comment_id', '=', $commentId], ['like_type', '=', "like"], ['is_post', '=', 0]])->count();
            $dislike = Like::where([['comment_id', '=', $commentId], ['like_type', '=', "dislike"], ['is_post', '=', 0]])->count();
            $angry = Like::where([['comment_id', '=', $commentId], ['like_type', '=', "angry"], ['is_post', '=', 0]])->count();
            $before_like = Like::where([['comment_id', '=', $commentId], ['is_post', '=', 0],['user_id','=',$user_id]])->first();

            if (!empty($before_like)) {
                if ($before_like->like_type == $likeType) {
                    $before_like->delete();
                    $like_emoji = ($likeType == "like") ? $like_emoji - 1 : $like_emoji;
                    $dislike = ($likeType == "dislike") ? $dislike - 1 : $dislike;
                    $angry = ($likeType == "angry") ? $angry - 1 : $angry;
                    //return response(compact('before_like'), 200);
                    $data = [
                        'like_id' => null,
                        'post_id' => $postId,
                        'comment_id' => $commentId,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => null,
                        'like_emoji' => $like_emoji,
                        'dislike' => $dislike,
                        'angry' => $angry,
                    ];

                } else {
                    $like_emoji = ($before_like->like_type == "like") ? $like_emoji - 1 : $like_emoji;
                    $dislike = ($before_like->like_type == "dislike") ? $dislike - 1 : $dislike;
                    $angry = ($before_like->like_type == "angry") ? $angry - 1 : $angry;
                    $before_like->delete();
                    $like = Like::create([
                        'post_id' => $postId,
                        'comment_id' => $commentId,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => $likeType
                    ]);
                    $like_emoji = ($likeType == "like") ? $like_emoji + 1 : $like_emoji;
                    $dislike = ($likeType == "dislike") ? $dislike + 1 : $dislike;
                    $angry = ($likeType == "angry") ? $angry + 1 : $angry;
                    $data = [
                        'like_id' => $like->id,
                        'post_id' => $postId,
                        'comment_id' => $commentId,
                        'user_id' => $user_id,
                        'is_post' => $isPost,
                        'like_type' => $likeType,
                        'like_emoji' => $like_emoji,
                        'dislike' => $dislike,
                        'angry' => $angry,
                    ];
                }
                $state = "good, ok";
                $message = "your data added successfully";

                return response(compact('state', 'message', 'data'), 200);

            }
            else{
                $like = Like::create([
                    'post_id' => $postId,
                    'comment_id' => $commentId,
                    'user_id' => $user_id,
                    'is_post' => $isPost,
                    'like_type' => $likeType
                ]);
                $like_emoji = ($likeType == "like") ? $like_emoji + 1 : $like_emoji;
                $dislike = ($likeType == "dislike") ? $dislike + 1 : $dislike;
                $angry = ($likeType == "angry") ? $angry + 1 : $angry;
                $state = "good, ok";
                $message = "your data added successfully";
                $data = [
                    'like_id' => $like->id,
                    'post_id' => $postId,
                    'comment_id' => $commentId,
                    'user_id' => $user_id,
                    'is_post' => $isPost,
                    'like_type' => $likeType,
                    'like_emoji' => $like_emoji,
                    'dislike' => $dislike,
                    'angry' => $angry,
                ];
                return response(compact('state', 'message', 'data'), 200);
            }
        }

        $state = "good, ok";
        $message = "your data added successfully";
        $data = [
        ];
        return response(compact('state', 'message', 'data'), 200);
    }
    public function get_like(){
        $post_id = $_GET['postId'];
        $user_id =Auth::user()->id;

        if(isset($_GET['commentId'])) {
            $comment_id = $_GET['commentId'];
            $like = Like::where([['post_id', $post_id],['comment_id',$comment_id],['user_id','=',$user_id]])->first();
            $state = "good, ok";
            $message = "information retreived successfully";
            if(!empty($like)){
                $data = [
                    'like_id' => $like->id,
                    'post_id' => $post_id,
                    'comment_id' => $like->comment_id,
                    'user_id' => $user_id,
                    'is_post' => $like->is_post,
                    'like_type' => $like->like_type,
                ];
            }
            else{
                $data=[];
            }
            return response(compact('state', 'message', 'data'), 200);
        }
        //only post
        $like = Like::where([['post_id', $post_id],['user_id','=',$user_id]])->first();
        $state = "good, ok";
        $message = "information retreived successfully";
        if(!empty($like)){
            $data = [
                'like_id' => $like->id,
                'post_id' => null,
                'comment_id' => $like->comment_id,
                'user_id' => $user_id,
                'is_post' => 1,
                'like_type' => $like->like_type,
            ];
        }
        else{
            $data=[];
        }
        return response(compact('state', 'message', 'data'), 200);

        }

}
