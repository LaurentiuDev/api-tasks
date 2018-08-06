<?php
namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use App\User;
use GenTux\Jwt\Drivers\FirebaseDriver;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Role;
class AdminController extends Controller {
    /* Add role
     * Edit role
     * Remove role
     * Check if admin  confirm password
     * Admin create user
     * Admin edit user
     * Admin delete user
     *
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function addRole(Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
        }



        $rules = [
            'name' => 'required|min:3'
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'be at least 3 characters long'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $roles = new Role([
            'name' => $request->input('name')
        ]);

        if($roles->save()) {
            $response = [
                'message' => 'Role was added',
                'role' => $roles
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function editRole($id,Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
        }

        $rules = [
            'name' => 'required|min:3'
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'be at least 3 characters long'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }

        $roles = Role::find($id);
        $roles->name = $request->input('name');

        if($roles->update()){
            $response = [
                'message' => 'Role was edited',
                'role' => $roles
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function deleteRole($id) {
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
        }



        $roles = Role::find($id);

        if($roles->delete())
        {
            $response = [
                'message' => 'Role was removed',
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function activateAccount($id,Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
        }


        $confirm = $request->input('confirm');

        if($confirm)
        {
            $user = User::where('id',$id)->get()->first();
            $user->status = 1 ;
            if($user->update())
            {
                $response = [
                  'message' => 'Account is active',
                  'user' =>$user
                ];

                return $this->returnSuccess($response);
            }
        }

        $response = [
            'message' => 'An error occured'
        ];

        return $this->returnError($response);
    }

    public function createUser(Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
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
        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'status' => $request->input('status'),
            'role_id' => $request->input('role_id')
        ]);

        $role_id = $request->input('role_id');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        $roleName = $role_name === 'admin' ? "Admin" : "User";
        if($user->save()) {

            $response =[
                'message' => $roleName.' created',
                'user' => $user
            ];

            return $this->returnSuccess($response);
        }
        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }



    public function editUser($id,Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
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

        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->status = $request->input('status');
        $user->role_id = $request->input('role_id');

        if($user->update()){
            $response =[
                'message' => 'User edited' ,
                'user' => $user
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function deleteUser($id){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user') {
            return $this->returnBadRequest();
        }


        $user = User::find($id);
        if($user->delete())
        {
            $response = [
              'message' => 'User was deleted'
            ];

            return $this->returnSuccess($response);
        }
    }
}
