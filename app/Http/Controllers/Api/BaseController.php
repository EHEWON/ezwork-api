<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

abstract class BaseController extends Controller {

    protected $lang='zh';

    public function getMessages(){
        return [];
    }

    public function getRules(){
        return [];
    }

    public function __construct(){
        $language=Request::header('language');
        $language=empty($language) ? 'zh' : $language;
        $this->lang=$language;
        App::setLocale($language);
    }

    /**
     * 登录
     */
    public function validate($params, $scene='') {
        $all_rules=$this->getRules();
        $all_messages=$this->getMessages();
        if(!empty($scene)){
            check(isset($all_rules[$scene]), Lang::get('common.validate_miss_rule'));
            check(isset($all_messages[$scene]), Lang::get('common.validate_miss_message'));
        }
        $rules=empty($scene) ? $all_rules : $all_rules[$scene];
        $messages=empty($scene) ? $all_messages : $all_messages[$scene];
        $validator=Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            $messages=$validator->errors()->messages();
            foreach ($messages as $message) {
                check(false, $message[0]);
            }
        }
    }
}
