<?php namespace Codejunkie\StarterKit\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Illuminate\Http\Request;
use AhmadFatoni\ApiGenerator\Helpers\Helpers;
use Illuminate\Support\Facades\Validator;
use Codejunkie\Starterkit\Components\ResetPassword as PasswordReset;
use Rainlab\User\Models\User;
use File;
use Mail;
use Carbon\Carbon;
use Image;
use ValidationException;
use Exception;
use Flash;

/**
 * Reset Password Back-end Controller
 */
class ResetPassword extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codejunkie.StarterKit', 'starterkit', 'resetpassword');
    }

    public function userResetPassword(Request $request){
        $user = User::where('email',$request['email'])->first();
        if($user){
            $reset = new PasswordReset;
            $reset->onRestorePasswordApi($user->email,$request['url']);
            return $user;
        }else{
            return 0;
        }
    }
}
