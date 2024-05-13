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
    protected $user_id=0;

    /**
     * 登录用户信息
     * @var array
     */
    protected $user;

    public function __construct(){

        parent::__construct();

        $token=Request::header('token');

        check(!empty($token), Lang::get('account.need_login'));

        try {
            $decrypted = Crypt::decryptString($token);
            $this->user_id=$decrypted;
        } catch (DecryptException $e) {
            check(!empty($token), Lang::get('account.re_login'));
        }
    }
}
