<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use Illuminate\Routing\Controller;

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
}
