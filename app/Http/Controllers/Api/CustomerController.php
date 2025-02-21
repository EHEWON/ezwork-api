<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Translate;
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
    public function info(Request $request, $customer_id) {
        $m_customer = new Customer();
        $data = $m_customer->getCustomerInfo($customer_id);
        ok($data);
    }

    function getGuestId() {
        $randUserId = $this->Guid();
        $count = Translate::where('rand_user_id', $randUserId)
                ->where('deleted_flag', 'N')
                ->count();
        while ($count > 0) {
            $randUserId = $this->Guid();
            $count = Translate::where('rand_user_id', $randUserId)
                    ->where('deleted_flag', 'N')
                    ->count();
        }
        ok($randUserId);
    }

    function Guid() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

}
