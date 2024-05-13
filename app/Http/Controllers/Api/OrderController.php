<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Order;


class OrderController extends BaseAuthController {

    /**
     * 关联订单列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_order=new Order();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_order->getRelatedOrders($this->user_id, $params['keyword'] ?? '', $page, $limit);
        ok($result);
    }

    /**
     * 关联订单的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $order_id){
        $m_order=new Order();
        $data=$m_order->getOrderInfo($this->user_id, $order_id);
        ok($data);
    }

    /**
     * 关联订单的详情信息
     * @param  Request $request 
     * @return 
     */
    public function detail(Request $request, $order_id){
        $m_order=new Order();
        $data=$m_order->getPurOrderDetail($order_id, $this->lang);
        ok($data);
    }
}
