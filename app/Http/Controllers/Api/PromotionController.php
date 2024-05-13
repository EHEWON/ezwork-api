<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Promotion;
use App\Models\PromotionRequest;
use App\Models\Setting;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewPromotionMail;

/**
 * 推广方案
 */
class PromotionController extends BaseAuthController {

    public function getMessages(){
        return [
            'add'=>[
                'content.required' => Lang::get('promotion.content_required'),
            ],
        ];
    }

    public function getRules(){
        return [
            'add'=>[
                'content'=>'required',
            ]
        ];
    }


    /**
     * 推广链接列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_promotion=new Promotion();
        $page=$request->get('page') ?? 1;
        $limit=$request->get('limit') ?? 20;
        $result=$m_promotion->getUserPromotions($this->user_id,$this->lang, $page, $limit);
        $m_user=new User();
        $user=$m_user->getUserInfo($this->user_id);
        foreach($result['data'] as &$res){
            $res->link='https://www3.ehewon.com/register.html?inviteid='.$user['invite_code'];
        }
        ok($result);
    }

    /**
     * 提交申请新的推广方案
     * @param  Request $request 
     * @return 
     */
    public function add(Request $request){
        $params=$request->post();
        $this->validate($params, 'add');

        $m_promotion_request=new PromotionRequest();
        $m_promotion_request->addPromotionRequest($this->user_id, $params['content']);

        try{
            $m_admin_user=new AdminUser();
            $m_setting=new Setting();
            $users=$m_setting->getSettingByAlias('notice_setting');
            if(!empty($users)){
                $emails=$m_admin_user->getEmailByUsers($users);
                if(!empty($emails)){
                    Mail::to($emails)->send(new NewPromotionMail());
                }
            }
        }catch(\Exception $e){
            
        }
        ok([]);
    }
}
