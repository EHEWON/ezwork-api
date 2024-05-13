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
            'setForeignBank'=>[
                'foreign_flag.required' => Lang::get('account.foreign_flag_required'),
                'foreign_flag.in' => Lang::get('account.foreign_flag_required'),
                'name.required' => Lang::get('account.bank_name_required'),
                'swift.required' => Lang::get('account.bank_swift_required'),
                'country.required' => Lang::get('account.bank_country_required'),
                'city.required' => Lang::get('account.bank_city_required'),
                'address.required' => Lang::get('account.bank_address_required'),
                'user.required' => Lang::get('account.bank_user_required'),
                'code.required' => Lang::get('account.bank_code_required'),
                'email.required' => Lang::get('account.bank_email_required'),
                'image.required' => Lang::get('account.bank_image_required'),
            ],
            'setBank'=>[
                'foreign_flag.required' => Lang::get('account.foreign_flag_required'),
                'foreign_flag.in' => Lang::get('account.foreign_flag_required'),
                'name.required' => Lang::get('account.bank_name_required'),
                'code.required' => Lang::get('account.bank_account_code_required'),
                'account.required' => Lang::get('account.bank_account_required'),
            ],
            'unload'=>[
                'code.required' => Lang::get('account.unload_bank_code_required'),
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
            'setForeignBank'=>[
                'foreign_flag' => 'required|in:N,Y',
                'name' => 'required',
                'swift' => 'required',
                'country' => 'required',
                'city' => 'required',
                'address' => 'required',
                'user' => 'required',
                'code' => 'required',
                'email' => 'required',
                'image' => 'required',
            ],
            'setBank'=>[
                'foreign_flag' => 'required|in:N,Y',
                'name' =>'required',
                'code' =>'required',
                'account' =>'required',
            ],
            'unload'=>[
                'code' =>'required',
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
     * 发送验证码(解绑银行卡)
     * @return 
     */
    public function sendByUnload(Request $request){

        $m_user=new User();
        $user=$m_user->getUserInfo($this->user_id);
        $email=$user['email'];
        $code=generateRandomInteger(6);
        $expired=(self::CODE_EXPIRED/60).Lang::get('common.minutes');

        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        try{
            Mail::to($email)->send(new UnloadBankMail($user));
            $m_send_code=new SendCode();
            $m_send_code->addUserSendCode($this->user_id, SendCode::UNLOAD_BANK_EMAIL, $email, $code);
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

    /**
     * 设置银行卡信息
     * @param  Request $request 
     * @return 
     */
    public function setBank(Request $request){
        $params=$request->post();
        if(!empty($params['foreign_flag']) && $params['foreign_flag']=='N'){
            $this->validate($params, 'setBank');
        }else{
            $this->validate($params, 'setForeignBank');
        }
        $m_bank=new Bank();
        $m_bank->editBank($this->user_id,$params);
        ok();
    }

    public function unloadBank(Request $request){
        $params=$request->post();
        $this->validate($params, 'unload');

        $code=$params['code'];
        $this->checkCode($code, SendCode::UNLOAD_BANK_EMAIL);

        $m_bank=new Bank();
        $m_bank->delUserBank($this->user_id);
        ok();
    }

    /**
     * 获取配置信息
     * @return array
     */
    public function setting(Request $request){
        $m_bank=new Bank();
        $m_user=new User();
        $user=$m_user->getUserInfo($this->user_id);
        $bank=$m_bank->getUserBank($this->user_id);
        ok([
            'message'=>[
                'email'=>$user['message_email'] ?? '',
                'calling_code'=>$user['message_calling_code'] ?? '',
                'phone'=>$user['message_phone'] ?? '',
            ],
            'bank'=>empty($bank) ? new \Stdclass() : $bank
        ]);
    }

    public function checkCode($code, $type){
        $m_send_code=new SendCode();
        $send_code=$m_send_code->getUserSendCode($this->user_id, $type);

        check(!empty($send_code), Lang::get('account.send_code_required'));
        check($send_code['code']==$code, Lang::get('account.send_code_invalid'));
        check(time()<strtotime($send_code['created_at'])+self::CODE_EXPIRED, Lang::get('account.send_code_expired'));
        $m_send_code->delUserSendCode($this->user_id, $type);
    }
}
