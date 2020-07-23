<?php namespace Codejunkie\Starterkit\Components;

use Cms\Classes\ComponentBase;
use Rainlab\User\Models\User;
use Rainlab\User\Models\UserGroup;
use Auth;
use Exception;
use Lang;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use RainLab\User\Models\User as UserModel;
use Redirect;
use URL;
use Flash;

class Admin extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Admin Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onAddOrEditUser() {
        $input = post();
        if(isset($input['user_id'])) {  //edit a user
            $id = $input['user_id'];
            $user = UserModel::find($id);
            $user->name = $input['name'];
            $user->username = $input['username'];
            if($user->groups[0]->code != $input['user_group']){
            }
            if(isset($input['password']) && isset($input['password_confirmation'])) {
                if($input['password'] == $input['password_confirmation']) {
                    $user->password = $input['password'];
                } else {
                    dd('Password did\'t match');
                }
            }
            $user->save();
        } else {    //Create new user
            if($input['password'] !== $input['password_confirmation']) {
                dd('Password didn\'t match');
            } else {
                $data = post();
                $rules = (new UserModel)->rules;
                $validation = Validator::make($data, $rules);
                if ($validation->fails()) {
                    throw new ValidationException($validation);
                }
                $user = Auth::register($data);
                $user->name = $input['name'];
                $user->username = $input['username'];
                $group = UserGroup::whereCode($input['user_group'])->first();
                $user->groups()->save($group);
                $user->save();
            }
        }
    } 

    public function onDeleteUser() {
        $id = post('id');
        $user = User::find($id);
        $user->forceDelete();
    }

}
