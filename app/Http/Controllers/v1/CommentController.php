<?php
namespace App\Http\Controllers\v1;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use App\Http\Controllers\Controller;
use App\Comment;
use App\Role;
class CommentController extends Controller {
    /*
     * Add comment
     * Edit comment
     * Remove comment
     */

    public function addComment($id,Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'body'     => 'required|min:1'
        ];

        $messages = [
            'body.required'  => 'Name empty',
            'body.length'    => 'be at least 1 character long'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }
        $id_user= Session::get('id_user');

        $comment = new Comment([
            'user_id' => $id_user,
            'task_id' => $id,
            'body' =>$request->input('body')
        ]);

        if($comment->save()) {
            $response = [
                'message' => 'Comment was added.',
                'comment' => $comment
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public  function editComment($id,Request $request) {
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'body'     => 'required|min:1'
        ];

        $messages = [
            'body.required'  => 'Name empty',
            'body.length'    => 'be at least 1 character long'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }


        $comment = Comment::find($id);
        if($comment->body === $request->input('body'))
        {
            $response = [
                'message' => 'Comment already exists'
            ];


            return $this->returnError($response);
        }
        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name !== 'admin') {
            if ($comment->user_id !== Session::get('id_user')) {
                $response = [
                    'message' => 'You cannot edit this comment'
                ];


                return $this->returnError($response);
            }
        }

        $comment->body = $request->input('body');

        if($comment->update()){
            $response = [
              'message' => 'The commnet was updated',
              'comment' => $comment
            ];

            return $this->returnSuccess($comment);
        }
        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function deleteComment($id){

        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $comment = Comment::find($id);

        if(! $comment) {
            $response = [
                'message' => 'Comment not found'
            ];

            return $this->returnNotFound($response);
        }


        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user')
        {
            $response = [
                'message' => 'Just admin can delete comments'
            ];
            return $this->returnError($response);
        }

        $comment->delete();

        $response = [
            'message' => 'Comment removed successfully'
        ];

        return $this->returnSuccess($response);
    }
}