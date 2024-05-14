<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;

/**
 * 关联客户
 */
class CustomerController extends BaseAuthController {


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
}
