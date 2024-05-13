<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Order;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

/**
 * 合伙人
 */
class UserController extends BaseAuthController {


    public function getMessages(){
        return [
            'edit'=>[
                'name.required' => Lang::get('account.name_required'),
                'country_id.required' => Lang::get('account.country_required'),
                'email.required' => Lang::get('account.email_required'),
                'agent_id.required' => Lang::get('account.agent_required'),
                'rate_commission.required' => Lang::get('account.rate_commission_required'),
            ],
            'setForeignBank'=>[
                'foreign_flag.required' => Lang::get('account.foreign_flag_required'),
                'foreign_flag.in' => Lang::get('account.foreign_flag_required'),
                'name.required' => Lang::get('account.bank_name_required'),
                'swift.required' => Lang::get('account.bank_swift_required'),
                'country.required' => Lang::get('account.bank_country_required'),
                'city.required' => Lang::get('account.bank_city_required'),
                'address.required' => Lang::get('account.bank_address_required'),
                'user.required' => Lang::get('account.bank_user_required'),
                'code.required' => Lang::get('account.bank_code_required'),
                'email.required' => Lang::get('account.bank_email_required'),
                'image.required' => Lang::get('account.bank_image_required'),
            ],
            'setBank'=>[
                'foreign_flag.required' => Lang::get('account.foreign_flag_required'),
                'foreign_flag.in' => Lang::get('account.foreign_flag_required'),
                'name.required' => Lang::get('account.bank_name_required'),
                'code.required' => Lang::get('account.bank_account_code_required'),
                'account.required' => Lang::get('account.bank_account_required'),
            ],
        ];
    }

    public function getRules(){
        return [
            'edit'=>[
                'name'=>'required',
                'country_id'=>'required',
                'email'=>'required',
                'agent_id'=>'required',
                'rate_commission'=>'required',
            ],
            'setForeignBank'=>[
                'foreign_flag' => 'required|in:N,Y',
                'name' => 'required',
                'swift' => 'required',
                'country' => 'required',
                'city' => 'required',
                'address' => 'required',
                'user' => 'required',
                'code' => 'required',
                'email' => 'required',
                'image' => 'required',
            ],
            'setBank'=>[
                'foreign_flag' => 'required|in:N,Y',
                'name' =>'required',
                'code' =>'required',
                'account' =>'required',
            ],
        ];
    }

    /**
     * 列表
     */
    public function index(Request $request) {
        $m_user=new User();
        $params=$request->input();
        $keyword=$request->get('keyword') ?? '';
        $user_id=$request->get('user_id') ?? '';
        $page=$request->get('page') ?? 1;
        $limit=$request->get('limit') ?? 20;
        $data=$m_user->getUsers($params, $this->lang, $page, $limit);
        ok($data);
    }

    /**
     * 获取用户信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $user_id){
        $m_user=new User();
        $m_bank=new Bank();
        $m_order=new Order();
        $m_admin_user=new AdminUser();
        $m_country=new Country();
        $user=$m_user->getUserInfo($user_id);
        if(empty($user)){
            ok();
        }
        unset($user['password']);
        $bank=$m_bank->getUserBank($user_id);
        $user['agent_id']=empty($user['agent_id']) ? '' : $user['agent_id'];
        $admin_user=$m_admin_user->getUser($user['agent_id']);
        $country_name=$m_country->getCountryName($user['country_id'],$this->lang);
        $user['bank']=$bank;
        $user['country_name']=$country_name;
        $user['total_commission']=$m_order->getUserCommissionTotal($user_id);
        $user['verify_commission']=$m_order->getVerifyCommissionTotal($user_id);
        $user['payed_commission']=$m_order->getPayedCommissionTotal($user_id);
        $user['agent_name']=$admin_user['realname'] ?? ($admin_user['username'] ?? '');
        ok($user);
    }


    public function edit(Request $request, $user_id){
        $params=$request->post();
        $this->validate($params, 'edit');
        if(!empty($params['bank']['foreign_flag']) && $params['bank']['foreign_flag']=='N'){
            $this->validate($params['bank'], 'setBank');
        }else{
            $this->validate($params['bank'], 'setForeignBank');
        }
        DB::transaction(function () use($user_id, $params) {
            $m_user=new User();
            $m_bank=new Bank();
            $m_user->editUser($user_id, $params);
            $m_bank->editBank($user_id,$params['bank']);
        });
        ok();
    }
}
