<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Translate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangePasswordMail;

class AccountController extends BaseAuthController {

    const CODE_EXPIRED  =   1800;

    public function getMessages(){
        return [
            'changepwd'=>[
                'oldpwd.required' => Lang::get('account.oldpwd_required'),
                'newpwd.required' => Lang::get('account.newpwd_required'),
                'newpwd.min' => Lang::get('account.newpwd_min'),
                'newpwd.confirmed' => Lang::get('account.newpwd_confirmed'),
            ],
            'changePwdByEmail'=>[
                'code.required' => Lang::get('account.email_code_required'),
                'newpwd.required' => Lang::get('account.newpwd_required'),
                'newpwd.min' => Lang::get('account.newpwd_min'),
                'newpwd.confirmed' => Lang::get('account.newpwd_confirmed'),
            ],
        ];
    }

    public function getRules(){
        return [
            'changepwd'=>[
                'oldpwd'=>'required',
                'newpwd'=>'required|min:6|confirmed',
            ],
            'changePwdByEmail'=>[
                'code'=>'required',
                'newpwd'=>'required|min:6|confirmed',
            ],
        ];
    }

    /**
     * 修改密码
     * @param  Request $request 
     * @return 
     */
    public function changePwd(Request $request){
        $params=$request->post();
        $this->validate($params, 'changepwd');

        $m_customer=new Customer();
        $customer=$m_customer->getCustomerInfo($this->customer_id);
        check(password_verify($params['oldpwd'], $customer['password']), Lang::get('account.oldpwd_not_match'));
        $m_customer->changePassword($this->customer_id, $params['newpwd']);
        ok();
    }

    /**
     * 发送验证码(修改密码)
     * @return 
     */
    public function send(Request $request){

        $m_customer=new Customer();
        $customer=$m_customer->getCustomerInfo($this->customer_id);
        $email=$customer['email'];
        $code=generateRandomInteger(6);
        $expired=(self::CODE_EXPIRED/60).Lang::get('common.minutes');

        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        try{
            Mail::to($email)->send(new ChangePasswordMail($user));
            $m_send_code=new SendCode();
            $m_send_code->addUserSendCode($this->customer_id, SendCode::CHANGE_PASSWORD_BY_EMAIL, $email, $code);
        }catch(\Exception $e){
            check(false, Lang::get('common.email_send_fail'));
        }
        
        ok();
    }

    /**
     * 通过邮箱修改密码
     * @return 
     */
    public function changePwdByEmail(Request $request){
        $params=$request->post();
        $this->validate($params, 'changePwdByEmail');

        $code=$params['code'];

        $this->checkCode($code, SendCode::CHANGE_PASSWORD_BY_EMAIL);

        $m_customer=new Customer();
        $m_customer->changePassword($this->customer_id, $params['newpwd']);
        ok();
    }

    /**
     * 存储空间使用情况
     * @return 
     */
    public function storage(){
        $m_translate=new Translate();
        $m_customer=new Customer();

        $customer=$m_customer->getCustomerInfo($this->customer_id);
        if(empty($this->customer_id)){
            ok([]);
        }
        $usedsize=$m_translate->getCustomerAllFileSize($this->customer_id);
        $storage=bcdiv($customer['storage'], 1048576, 2);
        $used=bcdiv($usedsize, 1048576, 2);
        ok([
            'storage'=>$storage,
            'used'=>$used,
            'percentage'=>bcmul(bcdiv($used, $storage, 3), 100),
        ]);
    }

    /**
     * 登录用户信息
     * @return 
     */
    public function info(){
        $m_customer=new Customer();
        $customer=$m_customer->getCustomerInfo($this->customer_id);
        if(empty($customer)){
            ok([]);
        }
        ok([
            'email'=>$customer['email'],
            'level'=>$customer['level'],
        ]);
    }
}
