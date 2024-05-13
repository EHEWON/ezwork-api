<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\CommissionWithdraw;

/**
 * 佣金
 */
class CommissionController extends BaseAuthController {

    /**
     * 关联佣金列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_order=new Order();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $keyword=$params['keyword'] ?? '';
        $created_at=$params['created_at'] ?? [];
        $result=$m_order->getOrderCommissions($this->user_id, $keyword, $created_at, $page, $limit);
        ok($result);
    }

    /**
     * 佣金统计数据
     * @param  Request $request 
     * @return 
     */
    public function count(Request $request){
        // $m_user=new User();
        $m_order=new Order();
        // $m_withdraw=new CommissionWithdraw();
        // $user=$m_user->getUserInfo($this->user_id);
        $total_commission=$m_order->getUserCommissionTotal($this->user_id);
        $verify_commission=$m_order->getVerifyCommissionTotal($this->user_id);
        $payed_commission=$m_order->getPayedCommissionTotal($this->user_id);
        // $total_withdraw=$m_withdraw->getUserWithdrawTotal($this->user_id);
        ok([
            // 'total_commission'=>$user['total_commission'],
            // 'aviable_commission'=>$user['aviable_commission'],
            'total_commission'=>$total_commission,
            'payed_commission'=>$payed_commission,
            'verify_commission'=>$verify_commission,
            // 'total_withdraw'=>$total_withdraw,
        ]);
    }
}
