<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model{

    protected $table = "setting";

    /**
     * 获取单个配置
     * @param  string  $alias 
     * @return array
     */
    public function getSettingByAlias($alias){

        $setting=$this->selectRaw('value,serialized')
            ->where('alias', $alias)
            ->first();
        if(empty($setting)){
            return [];
        }
        if($setting['serialized']){
            return json_decode($setting['value'],true);
        }
        return $setting['value'];
    }

    public function updateSettingByAlias($alias,$value){
        $serialized=0;
        if(is_array($value)){
            $value=json_encode($value);  
            $serialized=1;
        } 
        $this->updateOrInsert(['alias'=>$alias],[
            'value'=>$value,
            'serialized'=>$serialized,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }
}
