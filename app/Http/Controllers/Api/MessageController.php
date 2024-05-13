<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Models\User;
use App\Models\Message;

/**
 * 消息
 */
class MessageController extends BaseAuthController {

    /**
     * 消息列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_message=new Message();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $keyword=$params['keyword'] ?? '';
        $created_at=$params['created_at'] ?? [];
        $result=$m_message->getUserMessages($this->user_id, $this->lang, $page, $limit);
        ok($result);
    }

    /**
     * 消息详情
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request,$message_id){
        $m_message=new Message();
        $message=$m_message->getMessageInfo($this->user_id, $this->lang, $message_id);
        $m_message->setMessageRead($this->user_id, $message_id);
        ok($message);
    }

    /**
     * 设置消息推送设置
     * @param  Request $request 
     * @return 
     */
    public function setting(Request $request){

        $params=$request->post();
        // $this->validate($params, 'messagePush');

        $email=$params['email'] ?? '';
        $calling_code=$params['calling_code'] ?? '';
        $phone=$params['phone'] ?? '';

        $m_user=new User();
        $m_user->setMessagePush($this->user_id, $email, $calling_code, $phone);

        ok();
    }
}
