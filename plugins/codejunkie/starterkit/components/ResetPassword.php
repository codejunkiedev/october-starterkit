<?php namespace Codejunkie\Starterkit\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Lang;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use RainLab\User\Models\User as UserModel;
use Redirect;
use URL;
use Flash;

class ResetPassword extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'ResetPassword Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function code()
    {
        $routeParameter = $this->property('paramCode');

        if ($code = $this->param($routeParameter)) {
            return $code;
        }

        return get('reset');
    }

    //
    // AJAX
    //

    /**
     * Trigger the password reset email
     */
    public function onRestorePasswordApi($email,$myurl)
    {
        $user = UserModel::findByEmail($email);
        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
        $link = $this->makeResetUrlApi($code,$myurl);
        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];
        Mail::send('rainlab.user::mail.restore', $data, function($message) use ($user) {
            $message->to($user->email, $user->name);
        });
    }

    protected function makeResetUrlApi($code,$myurl)
    {
        $url = URL::to('/');
        $params = [
            $this->property('paramCode') => $code
        ];
        if (strpos($myurl, $code) === false) {
            $myurl = $url.'/reset?reset='.$code;
        }
        return $myurl;
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|between:' . UserModel::getMinPasswordLength() . ',255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $errorFields = ['code' => Lang::get(/*Invalid activation code supplied.*/'rainlab.user::lang.account.invalid_activation_code')];

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            throw new ValidationException($errorFields);
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !strlen(trim($code)) || !$code) {
            throw new ValidationException($errorFields);
        }

        if (!$user = Auth::findUserById($userId)) {
            throw new ValidationException($errorFields);
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            throw new ValidationException($errorFields);
        }
        Flash::success('Password has been changed successfuly');
    }

    //
    // Helpers
    //

    /**
     * Returns a link used to reset the user account.
     * @return string
     */
    protected function makeResetUrl($code)
    {
        $params = [
            $this->property('paramCode') => $code
        ];

        if ($pageName = $this->property('resetPage')) {
            $url = $this->pageUrl($pageName, $params);
        }
        else {
            $url = $this->currentPageUrl($params);
        }

        if (strpos($url, $code) === false) {
            $url .= '?reset=' . $code;
        }

        return $url;
    }
}
