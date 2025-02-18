<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
/**
 * 对照收藏表
 */
class PromptFav extends Model{

    protected $table = "prompt_fav";

    /**
     * 添加或取消
     */
    public function favPrompt($customer_id, $prompt_id){
        $exists=$this->where('customer_id',$customer_id)->where('prompt_id',$prompt_id)->exists();
        if($exists){
            $this->where('customer_id',$customer_id)->where('prompt_id',$prompt_id)->delete();
        }else{
            $this->insertGetId([
                'customer_id'=>$customer_id,
                'prompt_id'=>$prompt_id,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 取消收藏
     */
    public function cancelFavPrompt($customer_id, $prompt_id){
        $this->where('customer_id',$customer_id)->where('prompt_id',$prompt_id)->delete();
    }
}
