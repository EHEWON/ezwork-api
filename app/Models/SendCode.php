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
     * @param  int $user_id 
     * @return 
     */
    public function addRegisterEmailCode($email, $code){
        return $this->insert([
            'customer_id'=>0,
            'send_to'=>$email,
            'code'=>$code,
            'type'=>self::REGISTER_BY_EMAIL,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取注册时的邮箱验证码
     * @param  int $user_id 
     * @return 
     */
    public function getRegisterEmailCode($email){
        return $this->where('send_to',$email)
            ->where('type', self::REGISTER_BY_EMAIL)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $user_id 
     * @return 
     */
    public function delRegisterEmailCode($email){
        $this->where('send_to',$email)
            ->where('type', self::REGISTER_BY_EMAIL)
            ->delete();
    }

    /**
     * 忘记密码时的邮箱验证码
     * @param  int $user_id 
     * @return 
     */
    public function addFindEmailCode($email, $code){
        return $this->insert([
            'customer_id'=>0,
            'send_to'=>$email,
            'code'=>$code,
            'type'=>self::FIND_PASSWORD_BY_EMAIL,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取忘记密码时的邮箱验证码
     * @param  int $user_id 
     * @return 
     */
    public function getFindEmailCode($email){
        return $this->where('send_to',$email)
            ->where('type', self::FIND_PASSWORD_BY_EMAIL)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $user_id 
     * @return 
     */
    public function delFindEmailCode($email){
        $this->where('send_to',$email)
            ->where('type', self::FIND_PASSWORD_BY_EMAIL)
            ->delete();
    }

    /**
     * 添加记录
     * @param  int $user_id 
     * @return 
     */
    public function addUserSendCode($user_id, $type, $email, $code){
        return $this->insert([
            'customer_id'=>$user_id,
            'send_to'=>$email,
            'code'=>$code,
            'type'=>$type,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 获取验证码
     * @param  int $user_id 
     * @return 
     */
    public function getUserSendCode($user_id, $type){
        return $this->where('customer_id',$user_id)
            ->where('type', $type)
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * 删除验证码
     * @param  int $user_id 
     * @return 
     */
    public function delUserSendCode($user_id, $type){
        $this->where('customer_id',$user_id)
            ->where('type', $type)
            ->delete();
    }
}
