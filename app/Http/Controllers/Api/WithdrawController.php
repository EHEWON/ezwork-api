<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\Models\User;
use App\Models\Order;
use App\Models\CommissionWithdraw;

/**
 * 佣金提现
 */
class WithdrawController extends BaseAuthController {

    public function getMessages(){
        return [
            'apply'=>[
                'amount.required' => Lang::get('withdraw.amount_required'),
            ]
        ];
    }

    public function getRules(){
        return [
            'apply'=>[
                'amount'=>'required',
            ]
        ];
    }

    /**
     * 关联提现列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_withdraw=new CommissionWithdraw();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $keyword=$params['keyword'] ?? '';
        $timespan=$params['timespan'] ?? [];
        $result=$m_withdraw->getWithdrawCommissions($this->user_id, $keyword, $timespan, $page, $limit);
        ok($result);
    }

    /**
     * 申请提现
     * @param  Request $request 
     * @return 
     */
    public function apply(Request $request){
        $params=$request->post();
        $this->validate($params, 'apply');

        $m_withdraw=new CommissionWithdraw();
        $m_withdraw->applyWithdraw($this->user_id, $params['amount']);
        ok();
    }
}
