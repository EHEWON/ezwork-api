<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $query->whereNot('customer_id',0);
        if(!empty($params['keyword'])){
            $query->where(function($q) use($params){
                $keyword='%'.$params['keyword'].'%';
                $q->where('translate_no','like',$keyword)
                    ->orWhere('origin_filename','like',$keyword);
            });
        }

        if(!empty($params['customer_id'])){
            $query->where('customer_id', $params['customer_id']);
        }
        if(!empty($params['status'])){
            $query->where('t.status', $params['status']);
        }
        if(!empty($params['skip_uuids']) && is_array($params['skip_uuids'])){
            $query->whereNotIn('uuid', $params['skip_uuids']);
        }
        $total=$query->clone()->count();
        $query->selectRaw('t.id,t.translate_no,t.status,t.origin_filename,t.origin_filepath,t.lang,t.target_filepath,t.start_at,t.end_at,t.process,c.customer_no');
        $query->leftJoin('customer as c','c.id','=','t.customer_id');
        $query->skip(($page-1)*$limit)->limit($limit);
        $results=$query->orderBy('id','desc')->get()->toArray();
        foreach($results as &$result){
            $result->status_name=$this->getStatusName($result->status);
            $result->spend_time=spend_time($result->start_at, $result->end_at);
            $result->origin_filename=urldecode($result->origin_filename);
            $result->target_filepath_copy=$result->target_filepath;
            if(strtolower($result->status)=='done'){
                $result->target_filepath='/storage/'.trim($result->target_filepath,'/');
            }else{
                $result->target_filepath='';
                $result->spend_time='';
            }
        }
        return ['data'=>$results, 'total'=>$total];
    }

    /**
     * 开始翻译
     * @param  int $id
     */
    public function startTranslate($id){
        $this->where('id',$id)->update([
            'status'=>'process',
            'failed_reason'=>'',
            'start_at'=>date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 翻译结束
     * @param  int $id
     */
    public function endTranslate($id, $target_filesize){
        $this->where('id',$id)->update([
            'status'=>'done',
            'end_at'=>date('Y-m-d H:i:s'),
            'target_filesize'=>$target_filesize,
            'failed_count'=>0
        ]);
    }

    /**
     * 翻译失败
     * @param  int $id
     */
    public function failedTranslate($id, $reason){
        $this->where('id',$id)->increment('failed_count',1,[
            'status'=>'failed',
            'end_at'=>date('Y-m-d H:i:s'),
            'failed_reason'=>$reason,
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
            'target_filepath'=>$params['target_filepath'],
            'origin_filesize'=>$params['origin_filesize'],
            'uuid'=>$params['uuid'],
            'lang'=>$params['lang'],
            'model'=>$params['model'],
            'type'=>$params['type'],
            'backup_model'=>$params['backup_model'],
            'prompt'=>$params['prompt'],
            'api_url'=>$params['api_url'],
            'api_key'=>$params['api_key'],
            'threads'=>$params['threads'],
            'customer_id'=>$params['customer_id'],
            'origin_lang'=>$params['origin_lang'],
            'comparison_id'=>$params['comparison_id'],
            'prompt_id'=>$params['prompt_id'],
            'doc2x_flag'=>$params['doc2x_flag'],
            'doc2x_secret_key'=>$params['doc2x_secret_key'],
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

    /**
     * 多选删除数据
     * @param  int $id
     */
    public function deleteMoreTranslate($ids){
        $this->whereIn('id', $ids)->update(['deleted_flag'=>'Y']);
    }

    /**
     * 删除数据
     * @param  int $id
     */
    public function deleteCustomerTranslate($customer_id,$id){
        $this->where('id',$id)->where('customer_id',$customer_id)->update(['deleted_flag'=>'Y']);
    }

    public function deleteAllTranslate($customer_id){
        $this->where('customer_id',$customer_id)->update(['deleted_flag'=>'Y']);
    }

    public function getStatusName($status){
        switch(strtolower($status)){
            case 'none':return '未开始';
            case 'process':return '翻译中';
            case 'done':return '已完成';
            case 'failed':return '翻译失败';
        }
    }

    public function getCustomerAllFileSize($customer_id){
        $size=DB::table($this->table)
            ->selectRaw('SUM(origin_filesize+target_filesize) as size')
            ->where('customer_id',$customer_id)
            ->where('deleted_flag','N')
            ->value('size');
        return $size;
    }

    public function getFinishTotal(){
        return DB::table($this->table)
            ->where('status','done')
            ->where('deleted_flag','N')
            ->count();
    }

    public function downloadMoreTranslate($ids){
        $files = $this->whereIn('id', $ids)->pluck('target_filepath')->toArray();
        $zipName = implode('_', $ids) . '.zip';
        if (Storage::exists('download/' . $zipName)){
            return '/storage/download/' . $zipName;
        }
        $zipPath = storage_path('app/public/download/' . $zipName);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                if (Storage::exists($file)) {
                    $zip->addFile(storage_path('app/public/' . $file), $file);
                }
            }
            $zip->close();
        }

        return '/storage/download/' . $zipName;
    }
}
