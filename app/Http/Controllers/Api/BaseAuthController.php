<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

abstract class BaseAuthController extends BaseController {

    /**
     * 登录用户id
     * @var integer
     */ 
    protected $customer_id=0;

    /**
     * 登录用户信息
     * @var array
     */
    protected $customer;

    /**
     * 要跳过校验的方法
     * @var array
     */
    protected $skip_methods=[];

    public function __construct(){

        parent::__construct();

        $method=Request::method();
        $action=Request::segment(3);
        if(in_array($action, $this->skip_methods)) return;

        $token=Request::header('token');

        check(!empty($token), Lang::get('account.need_login'));

        try {
            $decrypted = Crypt::decryptString($token);
            $this->customer_id=$decrypted;
        } catch (DecryptException $e) {
            check(!empty($token), Lang::get('account.re_login'));
        }
    }
}
