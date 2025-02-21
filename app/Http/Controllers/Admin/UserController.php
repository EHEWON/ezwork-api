<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;

/**
 * 合伙人
 */
class UserController extends BaseAuthController {

    public function getMessages() {
        return [
            'edit' => [
                'name.required' => '请输入用户名',
                'email.required' => '请输入用户邮箱',
            ],
            'add' => [
                'name.required' => '请输入用户名',
                'email.required' => '请输入用户邮箱',
                'password.required' => '请输入用户密码',
                'password.min' => '密码必须大于6位字符串',
                'password.max' => '密码必须小于16位字符串',
            ],
        ];
    }

    public function getRules() {
        return [
            'edit' => [
                'name' => 'required',
                'email' => 'required',
            ],
            'add' => [
                'name' => 'required',
                'email' => 'required',
                'password' => 'required|min:6|max:16',
            ],
        ];
    }

    /**
     * 列表
     */
    public function index(Request $request) {
        $m_user = new User();
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? 20;
        $data = $m_user->getUsers($request, $page, $limit);
        ok($data);
    }

    /**
     * 获取用户信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $user_id) {
        $m_user = new User();
        $m_bank = new Bank();
        $m_order = new Order();
        $m_admin_user = new AdminUser();
        $m_country = new Country();
        $user = $m_user->getUserInfo($user_id);
        if (empty($user)) {
            ok();
        }
        unset($user['password']);
        $bank = $m_bank->getUserBank($user_id);
        $user['agent_id'] = empty($user['agent_id']) ? '' : $user['agent_id'];
        $admin_user = $m_admin_user->getUser($user['agent_id']);
        $country_name = $m_country->getCountryName($user['country_id'], $this->lang);
        $user['bank'] = $bank;
        $user['country_name'] = $country_name;
        $user['total_commission'] = $m_order->getUserCommissionTotal($user_id);
        $user['verify_commission'] = $m_order->getVerifyCommissionTotal($user_id);
        $user['payed_commission'] = $m_order->getPayedCommissionTotal($user_id);
        $user['agent_name'] = $admin_user['realname'] ?? ($admin_user['username'] ?? '');
        ok($user);
    }

    public function edited(Request $request, $user_id) {
        $params = $request->post();
        $this->validate($params, 'edit');
        $userModel = new User();
        $flag = $userModel->edited($user_id, $request);
        ok($flag);
    }

    public function added(Request $request) {
        $userModel = new User();
        $userId = $userModel->added($request);
        ok($userId);
    }

    public function deleted(Request $request) {
        $userModel = new User();
        return $userModel->deleteData($request);
    }

}
