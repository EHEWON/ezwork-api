<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class UploadController extends BaseAuthController {

    public function getMessages(){
        return [
            'del'=>[
                'filepath.required'=>Lang::get('upload.filepath_required')
            ]
        ];
    }

    public function getRules(){
        return [
            'del'=>[
                'filepath'=>'required'
            ]
        ];
    }

    /**
     * 
     */
    public function index(Request $request) {
        $file=$request->file('file');
        if($file->isValid()){
            $filesize=filesize($file->getPathname());
            if($this->customer_id!=0){
                $m_customer=new Customer();
                $avaiableStorage=$m_customer->getCustomerAvaiableStorage($this->customer_id);
                check($avaiableStorage>$filesize, '存储空间不足');
            }
            $ext=$file->getClientOriginalExtension();
            $hash = $file->hashName() ? $file->hashName() : Str::random(40);
            $datetime=date('ymd');
            $filename=explode('.', $hash)[0];
            $path=$file->storeAs('/uploads/'.$datetime, $filename.'.'.$ext);
            $uuid=create_uuid();
            ok([
                'filepath'=>'/'.$path,
                'filename'=>$file->getClientOriginalName(),
                'uuid'=>$uuid
            ]);
        }
        
        check(false, '文件上传失败');
    }

    public function del(Request $request){
        $params=$request->post();
        $this->validate($params, 'del');

        $filepath=$params['filepath'];
        $fullpath=storage_path('app/public'.$filepath);
        if(file_exists($fullpath)){
            unlink($fullpath);
        }

        ok();
    }
}
