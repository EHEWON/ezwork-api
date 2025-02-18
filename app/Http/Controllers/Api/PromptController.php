<?php

namespace App\Http\Controllers\Api;

use App\Models\Prompt;
use Illuminate\Http\Request;
use App\Models\Comparison;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use PhpOffice\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * 翻译对照表
 */
class PromptController extends BaseAuthController {

    protected $skip_methods=['template'];

    public function __construct(){
        parent::__construct();
        if(in_array($this->current_method, $this->skip_methods)){
            return;
        }
        check(!empty($this->customer_id), Lang::get('comparison.need_business_version'));
    }

    public function getMessages(){
        return [
            'add'=>[
                'title.required'=>Lang::get('prompt.title_required'),
                'content.required'=>Lang::get('prompt.content_required'),
            ],
            'edit_share'=>[
                'share_flag.required'=>Lang::get('prompt.share_flag_required'),
                'share_flag.in'=>Lang::get('prompt.share_flag_in'),
            ]
        ];
    }

    public function getRules(){
        return [
            'add'=>[
                'title'=>'required',
                'content'=>'required',
                'share_flag'=>'in:Y,N',
            ],
            'edit_share'=>[
                'share_flag'=>'required|in:Y,N',
            ]
        ];
    }


    public function my(Request $request){
        $params=$request->input();
        $m_comparison=new Prompt();
        $params['customer_id']=$this->customer_id;
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 10;
        $data=$m_comparison->getMyPrompts($params, $page, $limit);
        ok($data);
    }

    /**
     * 广场列表
     * @param  Request $request
     */
    public function share(Request $request){
        $params=$request->input();
        $m_comparison=new Prompt();
        $params['customer_id']=$this->customer_id;
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 10;
        $data=$m_comparison->getSharedPrompts($params, $page, $limit);
        ok($data);
    }

    /**
     * 添加对照数据
     */
    public function add(Request $request){
        $params=$request->post();
        $m_comparison=new Prompt();
        $params['customer_id']=$this->customer_id;
        $m_comparison->addPrompt($params);
        ok();
    }

    public function edit(Request $request, $prompt_id){
        $params=$request->post();
        $m_comparison=new Prompt();
        $params['customer_id']=$this->customer_id;
        $m_comparison->editPrompt($prompt_id,$params);
        ok();
    }

    /**
     * 更新分享状态
     * @param  Request $request
     */
    public function edit_share(Request $request,$prompt_id){
        $params=$request->post();
        $this->validate($params, 'edit_share');
        $m_comparison=new Prompt();
        $share_flag=$params['share_flag'];
        $m_comparison->editPromptShare($this->customer_id, $prompt_id, $share_flag);
        ok();
    }

    /**
     * 删除
     * @param  Request $request
     */
    public function del(Request $request,$prompt_id){
        $params=$request->post();
        $m_comparison=new Prompt();
        $m_comparison->delPrompt($this->customer_id, $prompt_id);
        ok();
    }

    /**
     * 加入我的提示语
     * @param  Request $request
     */
    public function copy(Request $request,$prompt_id){
        $params=$request->post();
        $m_comparison=new Prompt();
        $m_comparison->copyPrompt($this->customer_id, $prompt_id);
        ok();
    }

    /**
     * 收藏或取消收藏
     * @param  Request $request
     */
    public function fav(Request $request,$prompt_id){
        $params=$request->post();
        $m_comparison=new Prompt();
        $m_comparison->favPrompt($this->customer_id, $prompt_id);
        ok();
    }

}
