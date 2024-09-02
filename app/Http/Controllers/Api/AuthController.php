<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\SendCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use App\Mail\FindPasswordMail;
use App\Mail\NewUserMail;

class AuthController extends BaseController {

    const CODE_EXPIRED  =   1800;

    public function getMessages(){
        return [
            'register'=>[
                'email.required' => Lang::get('auth.email_required'),
                'password.required' => Lang::get('auth.password_required'),
                'password.min' => Lang::get('auth.password_min'),
                'code.required' => Lang::get('auth.code_required'),
            ],
            'login'=>[
                'email.required' => Lang::get('auth.email_required'),
                'password.required' => Lang::get('auth.password_required'),
            ],
            'sendByRegister'=>[
                'email.required' => Lang::get('auth.email_required'),
            ],
            'sendByFind'=>[
                'email.required' => Lang::get('auth.email_required'),
            ],
            'find'=>[
                'code.required' => Lang::get('account.email_code_required'),
                'password.required' => Lang::get('account.newpwd_required'),
                'password.min' => Lang::get('account.newpwd_min'),
                'password.confirmed' => Lang::get('account.newpwd_confirmed'),
            ],
        ];
    }

    public function getRules(){
        return [
            'register'=>[
                'email'=>'required',
                'password'=>'required|min:6',
                'code'=>'required',
            ],
            'login'=>[
                'email'=>'required',
                'password'=>'required',
            ],
            'sendByRegister'=>[
                'email'=>'required',
            ],
            'sendByFind'=>[
                'email'=>'required',
            ],
            'find'=>[
                'code'=>'required',
                'password'=>'required|min:6|confirmed',
            ],
        ];
    }

    /**
     * 发送验证码(注册时)
     * @return 
     */
    public function sendByRegister(Request $request){
        $params=$request->post();
        $this->validate($params, 'sendByRegister');

        $email=$params['email'];
        $this->check_email_limit($email);
        $m_customer=new Customer();
        $user=$m_customer->getCustomerByEmail($email);
        check(empty($user), Lang::get('auth.email_exists'));

        $code=generateRandomInteger(6);
        $expired=(self::CODE_EXPIRED/60).Lang::get('common.minutes');

        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        try{
            $content=Mail::to($email)->send(new RegisterMail($user));
            $m_send_code=new SendCode();
            $m_send_code->addRegisterEmailCode($email, $code);
        }catch(\Exception $e){
            check(false, Lang::get('common.email_send_fail'));
        }
        ok();
    }

    /**
     * 发送验证码(忘记密码)
     * @return 
     */
    public function sendByFind(Request $request){
        $params=$request->post();
        $this->validate($params, 'sendByFind');

        $email=$params['email'];
        $m_customer=new Customer();
        $user=$m_customer->getCustomerByEmail($email);
        check(!empty($user), Lang::get('auth.user_not_exists'));

        $code=generateRandomInteger(6);
        $expired=(self::CODE_EXPIRED/60).Lang::get('common.minutes');

        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        try{
            $content=Mail::to($email)->send(new FindPasswordMail($user));
            $m_send_code=new SendCode();
            $m_send_code->addFindEmailCode($email, $code);
        }catch(\Exception $e){
            check(false, Lang::get('common.email_send_fail'));
        }
        ok();
    }

    /**
     * 注册
     */
    public function register(Request $request) {
        $params=$request->post();
        $this->validate($params, 'register');
        $email=$params['email'];

        $m_setting=new Setting();
        $this->check_email_limit($email);
        $m_customer=new Customer();
        $exist=$m_customer->getCustomerByEmail($email);
        check(empty($exist), Lang::get('auth.user_exist'));

        $m_send_code=new SendCode();
        $send_code=$m_send_code->getRegisterEmailCode($email);

        check(!empty($send_code), Lang::get('auth.send_email_required'));
        check(time()<strtotime($send_code['created_at'])+self::CODE_EXPIRED, Lang::get('account.send_code_expired'));
        check($params['code']==$send_code['code'], Lang::get('auth.code_invalid'));

        $customer_id=$m_customer->registerCustomer($params);
        $m_send_code->delRegisterEmailCode($email);

        $token=Crypt::encryptString($customer_id);

        $users=$m_setting->getSettingByAlias('notice_setting');
        if(!empty($users)){
            $m_user=new User();
            $emails=$m_user->getEmailByUsers($users);
            if(!empty($emails)){
                $params = ['email' => $email];
                try{
                    $content=Mail::to($emails)->send(new NewUserMail($params));
                }catch(\Exception $e){
                }
            }
        }
        
        ok(['token'=>$token, 'email'=>$params['email']]);
    }

    /**
     * 登录
     */
    public function login(Request $request) {

        $params=$request->post();
        $this->validate($params, 'login');

        $email=$params['email'];
        $password=$params['password'];

        $m_customer=new Customer();
        $user=$m_customer->getCustomerByEmail($email);

        check(!empty($user), Lang::get('auth.user_not_exists'));
        check($user['status']=='enabled', '账户已禁用');
        check(password_verify($password, $user['password']), Lang::get('auth.password_invalid'));

        $token=Crypt::encryptString($user['id']);

        ok(['token'=>$token, 'email'=>$user['email'],'level'=>$user['level']]);
    }

    /**
     * 重置密码
     */
    public function find(Request $request) {

        $params=$request->post();
        $this->validate($params, 'find');

        $code=$params['code'];
        $email=$params['email'];

        $m_send_code=new SendCode();
        $send_code=$m_send_code->getFindEmailCode($email);

        check(!empty($send_code), Lang::get('account.send_code_required'));
        check($send_code['code']==$code, Lang::get('account.send_code_invalid'));
        check(time()<strtotime($send_code['created_at'])+self::CODE_EXPIRED, Lang::get('account.send_code_expired'));

        $m_customer=new Customer();
        $user=$m_customer->getCustomerByEmail($email);
        check(!empty($user), Lang::get('auth.user_not_exists'));
        $m_customer->changePassword($user['id'], $params['password']);
        $m_send_code->delFindEmailCode($email);
        ok();
    }

    public function check_email_limit($email){
        $m_setting=new Setting();
        $setting=$m_setting->getSettingByGroup('other_setting');
        if(!empty($setting['email_limit'])){
            $limits=explode(',', $setting['email_limit']);
            $match=array_filter($limits, function($item) use($email){
                return str_ends_with($email, $item);
            });
            check(count($match)>0, Lang::get('auth.email_not_allowed'));
        }
    }
}
