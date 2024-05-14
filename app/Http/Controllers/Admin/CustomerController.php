<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;

/**
 * 关联客户
 */
class CustomerController extends BaseAuthController {

    public function getMessages(){
        return [
            'edit'=>[
                'email.required' => Lang::get('customer.email_required'),
                'level.required' => Lang::get('customer.level_required'),
                'level.in' => Lang::get('customer.level_required'),
                'password.min' => Lang::get('customer.password_min'),
            ]
        ];
    }

    public function getRules(){
        return [
            'edit'=>[
                'email'=>'required',
                'level'=>'required|in:common,vip,COMMON,VIP',
                'password'=>'min:6'
            ]
        ];
    }


    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_customer=new Customer();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_customer->getCustomers($params, $page, $limit);
        ok($result);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $customer_id){
        $m_customer=new Customer();
        $data=$m_customer->getCustomerInfo($customer_id);
        ok($data);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function edit(Request $request, $customer_id){
        $params=$request->post();
        $this->validate($params, 'edit');
        $m_customer=new Customer();
        $data=$m_customer->editCustomer($customer_id, $params);
        ok($data);
    }
}
