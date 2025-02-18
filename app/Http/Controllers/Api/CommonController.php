<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;

class CommonController extends Controller {

    /**
     * 获取相关配置
     * @param  Request $request
     * @return
     */
    public function setting(Request $request){
        $m_setting=new Setting();
        $setting=$m_setting->getSettingByGroup('site_setting');
        ok([
            'version'=>$setting['version'] ?? 'business'
        ]);
    }

    /**
     * 获取并缓存所有配置信息
     * @param Request $request
     * @return void
     */
    public function getAllSettings(Request $request)
    {
        $redisKey = 'all_settings';

        // 尝试从Redis获取缓存数据
        $cachedSettings = Redis::get($redisKey);

        if (!empty($cachedSettings)) {
            // 如果存在缓存，直接返回
            ok(json_decode($cachedSettings, true));
            return;
        }

        // 如果没有缓存，从数据库读取
        $m_setting = new Setting();
        $settings = $m_setting->getAllSetting();

        // 将数据存入Redis（设置过期时间为1天）
        Redis::setex($redisKey, 86400, json_encode($settings));

        // 返回数据
        ok($settings);
    }
}
