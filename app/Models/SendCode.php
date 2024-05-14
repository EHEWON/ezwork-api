<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SendCode extends Model{

    protected $table = "send_code";

    const REGISTER_BY_EMAIL         =   1;
    const CHANGE_PASSWORD_BY_EMAIL  =   2;
    const FIND_PASSWORD_BY_EMAIL    =   3;
    const UNLOAD_BANK_EMAIL         =   4;

    /**
     * 添加注册时的邮箱验证码
     * @param  int $send_to 
     * @return 
     */
    public function addRegisterEmailCode($email, $code){
        return $this->insert([
            'send_to'=>0,
            'send_to'=>$email,
            'code'=>$code,
            'send_type'=>self::REGISTER_BY_EMAIL,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取注册时的邮箱验证码
     * @param  int $send_to 
     * @return 
     */
    public function getRegisterEmailCode($email){
        return $this->where('send_to',$email)
            ->where('send_type', self::REGISTER_BY_EMAIL)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $send_to 
     * @return 
     */
    public function delRegisterEmailCode($email){
        $this->where('send_to',$email)
            ->where('send_type', self::REGISTER_BY_EMAIL)
            ->delete();
    }

    /**
     * 忘记密码时的邮箱验证码
     * @param  int $send_to 
     * @return 
     */
    public function addFindEmailCode($email, $code){
        return $this->insert([
            'send_to'=>0,
            'send_to'=>$email,
            'code'=>$code,
            'send_type'=>self::FIND_PASSWORD_BY_EMAIL,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取忘记密码时的邮箱验证码
     * @param  int $send_to 
     * @return 
     */
    public function getFindEmailCode($email){
        return $this->where('send_to',$email)
            ->where('send_type', self::FIND_PASSWORD_BY_EMAIL)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $send_to 
     * @return 
     */
    public function delFindEmailCode($email){
        $this->where('send_to',$email)
            ->where('send_type', self::FIND_PASSWORD_BY_EMAIL)
            ->delete();
    }

    /**
     * 添加记录
     * @param  int $send_to 
     * @return 
     */
    public function addUserSendCode($send_to, $send_type, $email, $code){
        return $this->insert([
            'send_to'=>$send_to,
            'send_to'=>$email,
            'code'=>$code,
            'send_type'=>$send_type,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取验证码
     * @param  int $send_to 
     * @return 
     */
    public function getUserSendCode($send_to, $send_type){
        return $this->where('send_to',$send_to)
            ->where('send_type', $send_type)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $send_to 
     * @return 
     */
    public function delUserSendCode($send_to, $send_type){
        $this->where('send_to',$send_to)
            ->where('send_type', $send_type)
            ->delete();
    }
}
