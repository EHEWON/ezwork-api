<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\UserInquiry;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;

/**
 * 关联客户
 */
class CustomerController extends BaseAuthController {


    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_user_invite=new UserInvite();
        $params=$request->input();
        $keyword=$params['keyword'] ?? '';
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_user_invite->getPartnerCustomers($keyword, $page, $limit);
        ok($result);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $partner_user_id, $customer_id){
        $m_user_invite=new UserInvite();
        $data=$m_user_invite->getInviteUserInfo($partner_user_id, $customer_id);
        ok($data);
    }
}
