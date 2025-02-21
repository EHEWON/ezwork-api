<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;

/**
 * 关联客户
 */
class CustomerController extends BaseAuthController {

    public function getMessages() {
        return [
            'edit' => [
                'email.required' => '请输入邮箱',
                'level.required' => '请选择用户等级',
                'level.in' => '用户等级选择错误',
                'password.min' => '密码必须大于6位字符串',
                'password.max' => '密码必须小于16位字符串',
            ],
            'add' => [
                'email.required' => '请输入邮箱',
                'level.required' => '请选择用户等级',
                'level.in' => '用户等级选择错误',
                'password.min' => '密码必须大于6位字符串',
                'password.max' => '密码必须小于16位字符串',
            ],
            'status' => [
                'status.required' => '请选择状态',
                'status.in' => '状态选择错误',
            ]
        ];
    }

    public function getRules() {
        return [
            'edit' => [
                'email' => 'required',
                'level' => 'required|in:common,vip,COMMON,VIP',
                'password' => 'min:6|max:16'
            ],
            'add' => [
                'email' => 'required',
                'level' => 'required|in:common,vip,COMMON,VIP',
                'password' => 'min:6|max:16'
            ],
            'status' => [
                'status' => 'required|in:disabled,enabled,DISABLED,ENABLED',
            ]
        ];
    }

    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request) {
        $m_customer = new Customer();
        $params = $request->input();
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        $result = $m_customer->getCustomers($params, $page, $limit);
        ok($result);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $customer_id) {
        $m_customer = new Customer();
        $data = $m_customer->getCustomerInfo($customer_id);
        ok($data);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function edit(Request $request, $customer_id) {
        $params = $request->post();
        $this->validate($params, 'edit');
        $m_customer = new Customer();
        $data = $m_customer->editCustomer($customer_id, $params);
        ok($data);
    }

    /**
     * 关联客户的基本信息
     * @param  Request $request 
     * @return 
     */
    public function add(Request $request) {
        $params = $request->post();
        $this->validate($params, 'add');
        $m_customer = new Customer();
        $data = $m_customer->addCustomer($params);
        ok($data);
    }

    /**
     * 修改账户状态
     * @param  Request $request 
     * @return 
     */
    public function status(Request $request, $customer_id) {
        $params = $request->post();
        $this->validate($params, 'status');
        $m_customer = new Customer();
        $data = $m_customer->changeCustomerStatus($customer_id, $params['status']);
        ok($data);
    }

}
