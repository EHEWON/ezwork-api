<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
/**
 * 对照收藏表
 */
class ComparisonFav extends Model{

    protected $table = "comparison_fav";

    /**
     * 添加或取消翻译对照数据
     */
    public function favComparion($customer_id, $comparison_id){
        $exists=$this->where('customer_id',$customer_id)->where('comparison_id',$comparison_id)->exists();
        if($exists){
            $this->where('customer_id',$customer_id)->where('comparison_id',$comparison_id)->delete();
        }else{
            $this->insertGetId([
                'customer_id'=>$customer_id,
                'comparison_id'=>$comparison_id,
                'created_at'=>date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * 取消收藏 
     */
    public function cancelFavComparion($customer_id, $comparison_id){
        $this->where('customer_id',$customer_id)->where('comparison_id',$comparison_id)->delete();
    }
}
