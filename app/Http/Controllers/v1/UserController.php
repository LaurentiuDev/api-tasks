<?php

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\User;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Session;
class UserController extends Controller
{

    /**
     * Login User
     * Change info user
     * Logout User
     * @param Request $request
     * @param User $userModel
     * @param JwtToken $jwtToken
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GenTux\Jwt\Exceptions\NoTokenException
     */

    public function login(Request $request, User $userModel, JwtToken $jwtToken)
    {
        $token = Session::get('jwt');

        if($token){
            $response = [
                'message' => 'Please logout first.'
            ];
            return $this->returnNotFound($response);
        }
        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'Email empty',
            'email.email'    => 'Email invalid',
            'password.required'    => 'Password empty'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $user = $userModel->login($request->email, $request->password);

        if ( ! $user) {
            return $this->returnNotFound('User sau parola gresite');
        }

        $email = $request->input('email');
        $users = User::where('email',$email)->get()->first();

        if($users->status === 0)
        {
            return $this->returnNotFound('Your account is not active . Please wait until it is activated by an admin');
        }
        $token = $jwtToken->createToken($user);
        $data = [
            'user' => $user,
            'jwt'  => $token->token()
        ];

        Session::put('id_user',$user->id);
        Session::put('name_user',$user->name);
        Session::put('email_user',$user->email);
        Session::put('status_user',$user->status);
        Session::put('role_id_user',$user->role_id);
        Session::put('created_at_user',$user->created_at);
        Session::put('updated_at_user',$user->updated_at);
        Session::put('jwt',$token->token());



        return $this->returnSuccess($data);
    }
    public function chengeInfo(Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'name'     => 'required|min:3',
            'email'    => 'required|email',
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'be at least 3 characters long',
            'email.required' => 'Email empty',
            'email.email'    => 'Email invalid',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $user = User::where('id',Session::get('id_user'))->get()->first();

        $user_email = $user->email;
        if($user_email === $request->input('email'))
        {
            $response = [
                'message' => 'Email already exists'
            ];
            return $this->returnError($response);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if($user->save()){
            $response = [
                'message' => 'Your info was changed.',
                'user' => $user
            ];

            return $this->returnSuccess($response);
        }
        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function logout() {
        Session::flush();
        $response = [
            'message' => 'You have disconnected'
        ];
        return $this->returnSuccess($response);
    }



}