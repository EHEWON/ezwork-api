<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model{

    protected $table = "customer";

    public function registerUser($email,$password,$name,$country_id){
        return $this->insertGetId([
            'user_no'=>'P'.date('YmdHis').random_int(10000, 99999),
            'email'=>$email, 
            'password'=>password_hash($password, PASSWORD_DEFAULT),
            'name'=>$name, 
            'created_by'=>date('Y-m-d H:i:s'),
            'country_id'=>$country_id,
            'created_by'=>0,
            'deleted_flag'=>'N',
            'invite_code'=>generateRandomString(6)
        ]);
    }
}
