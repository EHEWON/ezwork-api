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
            'set_api'=>[
                'api_url.required' => Lang::get('setting.api_url_required'),
                'api_key.required' => Lang::get('setting.api_key_required'),
                'models.required' => Lang::get('setting.models_required'),
            ],
            'set_other'=>[
                'prompt.required' => Lang::get('setting.prompt_required'),
                'threads.required' => Lang::get('setting.threads_required'),
            ],
            'set_site'=>[
                'version.required' => Lang::get('setting.version_required'),
            ]
        ];
    }

    public function getRules(){
        return [
            'set_api'=>[
                'api_url'=>'required',
                'api_key'=>'required',
                'models'=>'required',
            ],
            'set_other'=>[
                'prompt'=>'required',
                'threads'=>'required',
            ],
            'set_site'=>[
                'version'=>'required',
            ]
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
        $m_setting->updateSettingByAlias('notice_setting','notice_setting', $users);
        ok();
    }

    /**
     * 获取api设置
     * @param  Request $request 
     * @return 
     */
    public function get_api(Request $request){
        $m_setting=new Setting();
        $result=$m_setting->getSettingByGroup('api_setting');
        ok($result);
    }

    /**
     * 设置api
     * @return 
     */
    public function set_api(Request $request){
        $params=$request->post();
        $this->validate($params, 'set_api');
        $m_setting=new Setting();
        foreach(['api_url','api_key','models','default_model','default_backup'] as $alias){
            $m_setting->updateSettingByAlias('api_setting',$alias, $params[$alias] ?? '');
        }
        ok();
    }

    /**
     * 获取其他设置
     * @param  Request $request 
     * @return 
     */
    public function get_other(Request $request){
        $m_setting=new Setting();
        $result=$m_setting->getSettingByGroup('other_setting');
        ok($result);
    }

    /**
     * 设置其他
     * @return 
     */
    public function set_other(Request $request){
        $params=$request->post();
        $this->validate($params, 'set_other');
        $m_setting=new Setting();
        foreach(['prompt','threads','email_limit'] as $alias){
            $m_setting->updateSettingByAlias('other_setting',$alias, $params[$alias] ?? '');
        }
        ok();
    }

    /**
     * 获取站点设置
     * @param  Request $request 
     * @return 
     */
    public function get_site(Request $request){
        $m_setting=new Setting();
        $result=$m_setting->getSettingByGroup('site_setting');
        ok($result);
    }

    /**
     * 设置站点
     * @return 
     */
    public function set_site(Request $request){
        $params=$request->post();
        $this->validate($params, 'set_site');
        $m_setting=new Setting();
        foreach(['version'] as $alias){
            $m_setting->updateSettingByAlias('site_setting',$alias, $params[$alias] ?? '');
        }
        ok();
    }
}
