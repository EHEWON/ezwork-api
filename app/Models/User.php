<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    protected $table = "user";

    public function getUser($user_id){
        if(empty($user_id)){
            return [];
        }
        $user=$this->where('id',$user_id)->where('deleted_flag','N')->first();
        return empty($user) ? [] : $user->toArray();
    }

    /**
     * 获取用户的邮箱
     * @param  array $user_id_arr 
     * @return 
     */
    public function getEmailByUsers($user_id_arr){
        if(empty($user_id_arr)) return [];
        return $this->whereIn('id', $user_id_arr)->pluck('email')->toArray();
    }

    /**
     * 获取邮箱
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function getAuthByEmail($email){
        if(empty($email)){
            return [];
        }
        $user=$this->where('email',$email)->where('deleted_flag','N')->first();
        return empty($user) ? [] : $user->toArray();
    }
}
