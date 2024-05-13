<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInvite;
use App\Models\UserInquiry;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Lang;

/**
 * 关联商机
 */
class InquiryController extends BaseAuthController {


    /**
     * 关联商机列表
     * @param  Request $request 
     * @return 
     */
    public function index(Request $request){
        $m_inquiry=new UserInquiry();
        $params=$request->input();
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 20;
        $result=$m_inquiry->getRelatedInquiries($this->user_id, $params['keyword'] ?? '', $page, $limit);
        ok($result);
    }

    /**
     * 关联商机的基本信息
     * @param  Request $request 
     * @return 
     */
    public function info(Request $request, $inquiry_id){
        $m_inquiry=new UserInquiry();
        $data=$m_inquiry->getInquiryInfo($this->user_id, $inquiry_id);
        ok($data);
    }

    /**
     * 关联商机的详情信息
     * @param  Request $request 
     * @return 
     */
    public function detail(Request $request, $inquiry_id){
        $m_inquiry=new Inquiry();
        $data=$m_inquiry->getInquiryInfo($inquiry_id,$this->lang);
        ok($data);
    }
}
