<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Translate extends Model{

    protected $table = "translate";

    /**
     * 获取用户列表
     * @param  array  $params 
     * @param  int $page   
     * @param  int $limit  
     */
    public function getTranslates($params, $page=1, $limit=20){
        $query=DB::table($this->table,'t')->where('t.deleted_flag','N');
        if(!empty($params['keyword'])){
            $query->where(function($q) use($params){
                $keyword='%'.$params['keyword'].'%';
                $q->where('translate_no','like',$keyword)
                    ->orWhere('origin_filename','like',$keyword);
            });
        }
        $total=$query->clone()->count();
        $query->selectRaw('t.id,t.translate_no,t.status,t.origin_filename,t.origin_filepath,t.target_filepath,t.start_at,t.end_at,c.customer_no');
        $query->leftJoin('customer as c','c.id','=','t.customer_id');
        $query->skip(($page-1)*$limit)->limit($limit);
        $results=$query->orderBy('id','desc')->get()->toArray();
        return ['data'=>$results, 'total'=>$total];
    }

    /**
     * 开始翻译
     * @param  int $id 
     */
    public function startTranslate($id){
        $this->where('id',$id)->update([
            'status'=>'process',
            'start_at'=>date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 翻译结束
     * @param  int $id 
     */
    public function endTranslate($id, $target_filepath){
        $this->where('id',$id)->update([
            'status'=>'done',
            'end_at'=>date('Y-m-d H:i:s'),
            'target_filepath'=>$target_filepath
        ]);
    }

    /**
     * 添加翻译记录
     * @param  string $email    
     * @param  string $password 
     * @return 
     */
    public function addTranslate($params){
        return $this->insertGetId([
            'translate_no'=>'T'.date('YmdHis').random_int(10000, 99999),
            'origin_filename'=>$params['origin_filename'], 
            'origin_filepath'=>$params['origin_filepath'], 
            'uuid'=>$params['uuid'],
            'customer_id'=>$params['customer_id'],
            'target_filepath'=>'', 
            'status'=>'none', 
            'created_at'=>date('Y-m-d H:i:s'),
            'deleted_flag'=>'N',
        ]);
    }

    public function getTranslateInfo($id){
        $translate=$this->where('id',$id)->first();
        return empty($translate) ? [] : $translate->toArray();
    }

    public function getTranslateInfoByUUID($uuid){
        $translate=$this->where('uuid',$uuid)->first();
        return empty($translate) ? [] : $translate->toArray();
    }

    /**
     * 删除数据
     * @param  int $id 
     */
    public function deleteTranslate($id){
        $this->where('id',$id)->update(['deleted_flag'=>'Y']);
    }
}
