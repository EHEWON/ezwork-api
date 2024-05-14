<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Crypt;

class AuthController extends BaseController {

    const CODE_EXPIRED  =   1800;

    public function getMessages(){
        return [
            'login'=>[
                'email.required' => Lang::get('auth.email_required'),
                'password.required' => Lang::get('auth.password_required'),
            ]
        ];
    }

    public function getRules(){
        return [
            'login'=>[
                'email'=>'required',
                'password'=>'required',
            ]
        ];
    }

    /**
     * 登录
     */
    public function login(Request $request) {

        $params=$request->post();
        $this->validate($params, 'login');

        $email=$params['email'];
        $password=$params['password'];

        $m_user=new User();
        $user=$m_user->getAuthByEmail($email);

        check(!empty($user), Lang::get('auth.user_not_exists'));
        check(password_verify($password, $user['password']), Lang::get('auth.password_invalid'));

        $token=Crypt::encryptString($user['id']);

        ok(['token'=>$token, 'name'=>$user['email']]);
    }
}
