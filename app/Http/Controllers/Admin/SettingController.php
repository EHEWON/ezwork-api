<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Models\Setting;

/**
 * 设置相关
 */
class SettingController extends BaseAuthController {

    public function getMessages(){
        return [
            
        ];
    }

    public function getRules(){
        return [
            
        ];
    }

    /**
     * 获取消息设置
     * @param  Request $request 
     * @return 
     */
    public function notice(Request $request){
        $m_setting=new Setting();
        $result=$m_setting->getSettingByAlias('notice_setting');
        $result=array_map(function($v){
            return intval($v);
        }, $result);
        ok($result);
    }

    /**
     * 提醒设置
     * @return 
     */
    public function notice_setting(Request $request){
        $params=$request->post();
        $users=($params['users'] && is_array($params['users'])) ? (array)$params['users'] : [];
        $m_setting=new Setting();
        $m_setting->updateSettingByAlias('notice_setting', $users);
        ok();
    }
}
