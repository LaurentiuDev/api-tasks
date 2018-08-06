<?php
namespace App\Http\Controllers\v1;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Role;

class RegistrationController extends Controller
{

    public function __construct()
    {
       // $this->middleware('guest');
    }

    /*
     *  Register user
     *  @param Request $request
     *  @return \Illuminate\Http\JsonResponse
     */


    public function register(Request $request) {
        $token = Session::get('jwt');

        if($token){
            $response = [
                'message' => 'Please logout first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'name'     => 'required|min:3',
            'email'    => 'required|email',
            'password' => 'required|min:5'
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'be at least 3 characters long',
            'email.required' => 'Email empty',
            'email.email'    => 'Email invalid',
            'password.required'    => 'Password empty',
            'password.length'      => 'be at least 5 characters long'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $email = $request->input('email');
        $users = User::where('email',$email)->get()->first();

        if($users) {
            $response = [
                'message' => 'Email address already  exists'
            ];

            return $this->returnError($response);
        }
        $roles = Role::where('name','admin')->get()->first();
        if(!$roles) {
            $roles = new Role([
                'name' => 'admin'
            ]);
            if(!$roles->save())
            {
                return $this->returnError('Error to save role');
            }
        }


        $role_id = $roles->id;
        $role_name = strtolower($roles->name);
        $existsAdmin = User::where('role_id',$role_id)->get()->first();

        $status = 1;
        if($existsAdmin)
        {
            $roles = Role::where('name','user')->get()->first();
            if(!$roles) {
                $roles = new Role([
                    'name' => 'user'
                ]);
                if(!$roles->save())
                {
                    return $this->returnError('Error to save role');
                }
            }
            $role_id= $roles->id;
            $status = 0;
        }

        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'status' => $status,
            'role_id' => $role_id
        ]);

        $role = $role_name === 'user' ? "User" : "Admin";

        if($user->save()) {
            if($user->status === 0) {
                $responsee = [
                    'status' => 'Your account is not active . Please wait until it is activated by an admin',
                    'message' => 'User created',
                    'user' => $user
                ];
                return $this->returnSuccess($responsee);
            }

            $response =[
                'message' => $role.' created',
                'user' => $user
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

}