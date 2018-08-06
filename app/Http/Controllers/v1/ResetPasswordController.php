<?php
namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Mail\LinkReset;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller {

    /*Send email to the user
     *Link to password reset
     */
    protected $hash;

    public function sentEmail(Request $request){
        $token = Session::get('jwt');

        if($token){
            $response = [
                'message' => 'Please logout first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'email'    => 'required|email'
        ];

        $message = [
            'email.required' => 'Email empty',
            'email.email'    => 'Email invalid'
        ];

        $validator = Validator::make($request->all(), $rules, $message);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }
        $user = $request->input('email');
        $this->hash =str_random(15);
        $users = User::where('email',$user)->get()->first();
        $users->{'hash'} = $this->hash;
        Session::put('email',$user);
        Session::put('hash',$this->hash);
        $this->hash = Session::get('hash');

        Mail::to($user)->send(new LinkReset($users));

    }

    public function reset($link,Request $request){
        $token = Session::get('jwt');

        if($token){
            $response = [
                'message' => 'Please logout first.'
            ];
            return $this->returnNotFound($response);
        }
        $hash=Session::get('hash');
        $email=Session::get('email');

        if($hash === $link) {

            $user = User::where('email',$email)->get()->first();
            $oldPassword = $user->password;
            $inputPassword = $request->input('oldPassword');
            $newPassword= $request->input('newPassword');
            $rules = [
                'oldPassword'    => 'required',
                'newPassword'    => 'required'
            ];

            $message = [
                'oldPassword.required' => 'oldPassword empty',
                'newPassword.required'    => 'newPassword empty',
                'oldPassword.confirmed'  =>'Passwords do not match'
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ( ! $validator->passes()) {
                return $this->returnNotFound("Error to validate");
            }

            if(app('hash')->check($inputPassword,$oldPassword)){

                $userUpdate = User::find($user->id);
                $userUpdate->password = Hash::make($newPassword);
                $userUpdate->save();
                $response = [
                    "message" => "Your password was changed ! ",
                    "user" => $userUpdate
                ];

                return $this->returnSuccess($response);
            } else {
                return $this->returnNotFound("Incorrect old password !");
            }

        }else {
            return $this->returnNotFound('Invalid link');
        }
    }



}