<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Translate;
use Illuminate\Support\Facades\Lang;

/**
 * 翻译记录
 */
class TranslateController extends BaseAuthController {

    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function start(Request $request){
        $m_customer=new Translate();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_customer->getTranslates($params, $page, $limit);
        ok($result);
    }
}
