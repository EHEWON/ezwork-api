<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\UserInquiry;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SendCode;
use App\Models\Bank;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangePasswordMail;
use App\Mail\UnloadBankMail;

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
     * 统计数据
     */
    public function index(Request $request) {
        $m_user=new User();
        $m_user_invite=new UserInvite();
        $m_user_inquiry=new UserInquiry();
        $m_order=new Order();
        $m_setting=new Setting();
        // $user=$m_user->getUserInfo($this->user_id);
        $user_count=$m_user_invite->getInviteUserCount($this->user_id);
        $inquiry_count=$m_user_inquiry->getRelatedInquiryCount($this->user_id);
        $order_count=$m_order->getRelatedOrderCount($this->user_id);
        $remark=$m_setting->getSettingByAlias('partner_remark');
        $remark=$remark[$this->lang] ?? '';

        $total_commission=$m_order->getUserCommissionTotal($this->user_id);
        $payed_commission=$m_order->getPayedCommissionTotal($this->user_id);

        ok([
            'user_count'=>$user_count,
            'inquiry_count'=>$inquiry_count,
            'order_count'=>$order_count,
            'total_commission'=>$total_commission,
            'payed_commission'=>$payed_commission,
            'remark'=>$remark
        ]);
    }

    /**
     * 修改密码
     * @param  Request $request 
     * @return 
     */
    public function changePwd(Request $request){
        $params=$request->post();
        $this->validate($params, 'changepwd');

        $m_user=new User();
        $user=$m_user->getUserInfo($this->user_id);
        check(password_verify($params['oldpwd'], $user['password']), Lang::get('account.oldpwd_not_match'));
        $m_user->changePassword($this->user_id, $params['newpwd']);
        ok();
    }

    /**
     * 发送验证码(修改密码)
     * @return 
     */
    public function send(Request $request){

        $m_user=new User();
        $user=$m_user->getUserInfo($this->user_id);
        $email=$user['email'];
        $code=generateRandomInteger(6);
        $expired=(self::CODE_EXPIRED/60).Lang::get('common.minutes');

        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        try{
            Mail::to($email)->send(new ChangePasswordMail($user));
            $m_send_code=new SendCode();
            $m_send_code->addUserSendCode($this->user_id, SendCode::CHANGE_PASSWORD_BY_EMAIL, $email, $code);
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

        $m_user=new User();
        $m_user->changePassword($this->user_id, $params['newpwd']);
        ok();
    }
}
