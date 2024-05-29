<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class UploadController extends BaseController {

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
            $ext=$file->getClientOriginalExtension();
            $hash = $file->hashName() ? $file->hashName() : Str::random(40);
            $filename=explode('.', $hash)[0];
            $path=$file->storeAs('uploads', $filename.'.'.$ext);
            $uuid=create_uuid();
            ok([
                'filepath'=>$path,
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
        $fullpath=storage_path('app/public/'.$filepath);
        if(file_exists($fullpath)){
            unlink($fullpath);
        }

        ok();
    }
}
