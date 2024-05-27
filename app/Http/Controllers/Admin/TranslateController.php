<?php

namespace App\Http\Controllers\Admin;

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
    public function index(Request $request){
        $m_customer=new Translate();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_customer->getTranslates($params, $page, $limit);
        ok($result);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $id){
        $m_customer=new Translate();
        $data=$m_customer->getTranslateInfo($id);
        ok($data);
    }

    /**
     * 删除数据
     * @param  Request $request 
     * @return 
     */
    public function delete(Request $request, $id){
        $m_customer=new Translate();
        $m_customer->deleteTranslate($id);
        ok();
    }
}
