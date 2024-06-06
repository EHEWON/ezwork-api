<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Translate;
use Illuminate\Support\Facades\Lang;

/**
 * 翻译记录
 */
class TranslateController extends BaseAuthController {

    public function getMessages(){
        return [
            'start'=>[
                'server.required'=>Lang::get('translate.server_required'),
                'server.in'=>Lang::get('translate.server_required'),
                'api_url.required_if'=>Lang::get('translate.api_url_required'),
                'api_key.required_if'=>Lang::get('translate.api_key_required'),
                'model.required'=>Lang::get('translate.model_required'),
                'lang.required'=>Lang::get('translate.lang_required'),
                'type.required'=>Lang::get('translate.type_required'),
                'uuid.required'=>Lang::get('translate.uuid_required'),
                'system.required'=>Lang::get('translate.system_required'),
                'threads.required'=>Lang::get('translate.threads_required'),
                'file_name.required'=>Lang::get('translate.file_name_required'),
                'file_path.required'=>Lang::get('translate.file_path_required'),
            ],
            'process'=>[
                'uuid.required'=>Lang::get('translate.uuid_required')
            ],
            'check'=>[
                'server.required'=>Lang::get('translate.server_required'),
                'server.in'=>Lang::get('translate.server_required'),
                'api_url.required_if'=>Lang::get('translate.api_url_required'),
                'api_key.required_if'=>Lang::get('translate.api_key_required'),
                'model.required'=>Lang::get('translate.model_required'),
            ],
        ];
    }

    public function getRules(){
        return [
            'start'=>[
                'server'=>'required|in:openai,member',
                'api_url'=>'required_if:server,openai',       
                'api_key'=>'required_if:server,openai',       
                'model'=>'required',
                'lang'=>'required',
                'type'=>'required',
                'uuid'=>'required',
                'system'=>'required',
                'threads'=>'required',
                'file_path'=>'required',
                'file_name'=>'required',
            ],
            'process'=>[
                'uuid'=>'required',
            ],
            'check'=>[
                'server'=>'required|in:openai,member',
                'api_url'=>'required_if:server,openai',       
                'api_key'=>'required_if:server,openai',       
                'model'=>'required',
            ],
        ];
    }


    public function index(Request $request){
        $params=$request->input();
        $m_translate=new Translate();
        $params['customer_id']=$this->customer_id;
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 10;
        $data=$m_translate->getTranslates($params, $page, $limit);
        ok($data);
    }

    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function start(Request $request){

        $params=$request->post();
        $this->validate($params, 'start');

        $translate_main=base_path('python/translate/main.py');
        $file_path=storage_path('app/public/'.$params['file_path']);
        $file_name=$params['file_name'];
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $filename = pathinfo($file_name,  PATHINFO_FILENAME);
        $target_file=storage_path('app/public/translate/'.$filename.'-'.$params['lang'].'.'.$extension);
        $target_path='/storage/translate/'.urlencode($filename.'-'.$params['lang'].'.'.$extension);
        $process_file=storage_path('app/public/process/'.$params['uuid'].'.txt');
        $lang=$params['lang'];
        $model=$params['model'];
        $system=str_replace('{target_lang}', $lang, $params['system']);
        $threads=$params['threads'];
        $api_url=$params['api_url'] ?? '';
        $api_key=$params['api_key'] ?? '';
        if($params['server']=='member'){
            $api_url=config('openai.api_url');
            $api_key=config('openai.api_key');
        }

        $m_translate=new Translate();
        $id=$m_translate->addTranslate([
            'origin_filename'=>$file_name,
            'origin_filepath'=>$params['file_path'],
            'origin_filesize'=>filesize($file_path),
            'customer_id'=>$this->customer_id,
            'uuid'=>$params['uuid'],
        ]);
        $m_translate->startTranslate($id);
        // echo "python3 $translate_main -f $file_path -o $target_file -l $lang --model $model --system '$system' --threads $threads --processfile $process_file --api_url $api_url --api_key $api_key --output_url '$target_path'";
        // exit;
        $cmd = shell_exec("python3 $translate_main -f $file_path -o $target_file -l $lang --model $model --system '$system' --threads $threads --processfile $process_file --api_url $api_url --api_key $api_key --output_url '$target_path'");
        echo $cmd;
        if($this->checkEndTranslate($params['uuid'])){
            $m_translate->endTranslate($id, $target_path, filesize($target_file));
        }

        ok();
    }

    public function process(Request $request){
        $params=$request->post();
        $this->validate($params, 'process');

        $uuid=$params['uuid'];
        $file=storage_path('app/public/process/'.$uuid.'.txt');
        $process=0;
        $url='';
        $count=0;
        $spend='';
        if(file_exists($file)){
            $content=file_get_contents($file);
            if(!empty($content)){
                $values=explode('$$$', $content);
                if(count($values)==2){
                    $process=intval($values[1])/intval($values[0]);
                }else if(count($values)>2){
                    $process=intval($values[1])/intval($values[0]);
                    $url=$values[2];
                    $count=$values[3];
                    $spend=$values[4];
                    $m_translate=new Translate();
                    $translate=$m_translate->getTranslateInfoByUUID($uuid);
                    if($translate && strtolower($translate['status'])!='done'){
                        $target_path=storage_path('app/public/'.urldecode(str_replace('/storage/', '', $url)));
                        $m_translate->endTranslate($translate['id'], $url, filesize($target_path));
                    }
                }
            }else{
                $process='';
            }
        }else{
            $process='';
        }

        ok(['process'=>$process,'url'=>$url,'count'=>$count,'time'=>$spend]);        
    }

    public function check(Request $request){
        $params=$request->post();
        $this->validate($params, 'check');

        if($params['server']=='member'){
            $api_url=config('openai.api_url');
            $api_key=config('openai.api_key');
        }
        // init_openai(api_url, api_key)
        // status=openai_check(model)
        // if status:
        //     return JsonResponse({"code":0, "msg":"success"})
        // else:
        //     return JsonResponse({"code":1, "msg":"fail"})
    }

    public function del(Request $request, $id){
        $m_translate=new Translate();
        $m_translate->deleteCustomerTranslate($this->customer_id,$id);
        ok();
    }

    public function delAll(Request $request){
        $m_translate=new Translate();
        $m_translate->deleteAllTranslate($this->customer_id);
        ok();
    }

    private function checkEndTranslate($uuid){
        $file=storage_path('app/public/process/'.$uuid.'.txt');
        if(file_exists($file)){
            $content=file_get_contents($file);
            if(!empty($content)){
                $values=explode('$$$', $content);
                if(count($values)>2){
                    return true;
                }
            }
        }
        return false;
    }
}
