<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\User;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\History;
use App\Notification;
use App\Role;
class TasksController extends Controller {
    /*
     * Get tasks
     * User or Admin Add task
     * User or Admin edit task
     * Admin delete task
     */
    public function getTasks(){
        $task = Task::all();
        $this->returnSuccess($task);
    }

    public function addTask(Request $request){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'name'     => 'required|min:3',
            'description'    => 'required|min:10',
            'status' => 'required'
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'Be at least 3 characters long',
            'description.required' => 'Description empty',
            'description.length'    => 'Description invalid',
            'status.required'    => 'Status empty'

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( !$validator->passes()) {
            return $this->returnBadRequest();
        }

        $task = new Task([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'assign' => $request->input('assign'),
            'user_id' => Session::get('id_user')
        ]);



        if($task->save())
        {
            $lastTask = Task::where('name' ,$request->input('name'))->get()->last();

            $history = new History([
                'task_id' => $lastTask->id,
                'assign' => $request->input('assign'),
                'status' => $request->input('status')
            ]);

            $history->save();

            $notification = new Notification([
                'task_id' => $lastTask->id,
                'user_id' => Session::get('id_user') ,
                'assign' => $request->input('assign'),
                'body' => Session::get('name_user'). ' has assigned you a task'
            ]);
            if(!$notification->save())
            {
                return $this->returnBadRequest();
            }

            $response =[
                'message' => 'Task created',
                'task' => $task,
                'notification' =>$notification
            ];

            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }



    public function editTask(Request $request , $id) {
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $rules = [
            'name'     => 'required|min:3',
            'description'    => 'required|min:10',
            'status' => 'required'
        ];

        $messages = [
            'name.required'  => 'Name empty',
            'name.length'    => 'Be at least 3 characters long',
            'description.required' => 'Description empty',
            'description.length'    => 'Description invalid',
            'status.required'    => 'Status empty'

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ( ! $validator->passes()) {
            return $this->returnBadRequest();
        }
        $task = Task::find($id);

        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($task->user_id !== Session::get('id_user') && $role_name !== 'admin')
        {
            $response = [
                'message' => 'You can edit just own task'
            ];

            return $this->returnError($response);
        }

        $task->name = $request->input('name');
        $task->description= $request->input('description');
        $task->status= $request->input('status');
        $task->assign= $request->input('assign');

        $history = new History([
            'task_id' => $id,
            'assign' => $request->input('assign'),
            'status' => $request->input('status')
        ]);

        $history->save();

        if($task->update()){

            $notification = new Notification([
                'task_id' => $id,
                'user_id' => Session::get('id_user') ,
                'assign' => $request->input('assign'),
                'body' => Session::get('name_user'). ' has assigned you a task'
            ]);
            if(!$notification->save())
            {
                return $this->returnBadRequest();
            }
            $response = [
                'message' => 'Task edited',
                'task' => $task,
                'notification' => $notification
            ];
            return $this->returnSuccess($response);
        }

        $response = [
            'message' => 'An error occured'
        ];


        return $this->returnError($response);
    }

    public function delete($id){
        $token = Session::get('jwt');

        if(!$token){
            $response = [
                'message' => 'Please login first.'
            ];
            return $this->returnNotFound($response);
        }

        $task = Task::find($id);

        if(!$task) {
            $response = [
                'message' => 'Task not found'
            ];
            return $this->returnNotFound($response);
        }



        $role_id = Session::get('role_id_user');
        $role = Role::find($role_id);
        $role_name = strtolower($role->name);

        if($role_name === 'user')
        {
            $response = [
                'message' => 'Just admin can delete tasks'
            ];
            return $this->returnError($response);
        }

        $task->delete();

        $response = [
            'message' => 'Task removed successfully'
        ];

        return $this->returnSuccess($response);
    }

}