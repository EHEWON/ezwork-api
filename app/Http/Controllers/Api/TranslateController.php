<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Translate;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * 翻译记录
 */
class TranslateController extends BaseAuthController {

    protected $skip_methods=['setting'];

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
                'prompt.required'=>Lang::get('translate.prompt_required'),
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
                'prompt'=>'required',
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

    public function setting(){
        $m_setting=new Setting();
        $api_setting=$m_setting->getSettingByGroup('api_setting');
        $other_setting=$m_setting->getSettingByGroup('other_setting');
        $api_setting['models']=explode(',', $api_setting['models'] ?? '');
        unset($api_setting['api_url']);
        unset($api_setting['api_key']);
        return ok(array_merge($api_setting, $other_setting));
    }

    /**
     * 关联客户列表
     * @param  Request $request 
     * @return 
     */
    public function start(Request $request){
        @set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');

        $params=$request->post();
        $this->validate($params, 'start');

        $translate_main=base_path('python/translate/main.py');
        $origin_filepath=$params['file_path'];
        $upload_filename=$params['file_name'];
        $uuid=$params['uuid'];
        $extension = pathinfo($upload_filename, PATHINFO_EXTENSION);
        $filename = pathinfo($upload_filename,  PATHINFO_FILENAME);
        $storage_path=storage_path('app/public');
        $target_filepath=sprintf('/translate/%s/%s', basename(pathinfo($origin_filepath,  PATHINFO_FILENAME), $extension),$filename.'-'.$params['lang'].'.'.$extension);
        $origin_storage_path=$storage_path.$origin_filepath;
        $target_storage_path=$storage_path.$target_filepath;
        $target_url='/storage/'.$target_filepath;
        $target_dir=pathinfo($target_storage_path, PATHINFO_DIRNAME);
        // echo $target_dir;
        @mkdir($target_dir);

        $process_file=$storage_path.'/process/'.$uuid.'.txt';
        $lang=$params['lang'];
        $model=$params['model'];
        $type=(!empty($params['type']) && is_array($params['type'])) ? array_pop($params['type']) : '';
        $backup_model=$params['backup_model'] ?? '';
        $prompt=str_replace('{target_lang}', $lang, $params['prompt']);
        $threads=$params['threads'];
        $api_url=$params['api_url'] ?? '';
        $api_key=$params['api_key'] ?? '';
        $m_setting=new Setting();
        if($params['server']=='member'){
            $api_setting=$m_setting->getSettingByGroup('api_setting');
            if(!empty($api_setting)){
                $api_url=$api_setting['api_url'];
                $api_key=$api_setting['api_key'];
            }else{
                $api_url=config('openai.api_url');
                $api_key=config('openai.api_key');
            }
        }

        $m_translate=new Translate();
        $id=$m_translate->addTranslate([
            'origin_filename'=>$upload_filename,
            'origin_filepath'=>$origin_filepath,
            'target_filepath'=>$target_filepath,
            'origin_filesize'=>filesize($origin_storage_path),
            'customer_id'=>$this->customer_id,
            'uuid'=>$uuid,
            'lang'=>$lang,
            'model'=>$model,
            'type'=>$type,
            'backup_model'=>$backup_model,
            'prompt'=>$prompt,
            'api_url'=>$api_url,
            'api_key'=>$api_key,
            'threads'=>$threads,
        ]);
        $m_translate->startTranslate($id);
        echo "python3 $translate_main $uuid $storage_path".PHP_EOL;
        $cmd = shell_exec("python3 $translate_main $uuid $storage_path");
        echo $cmd;
        // if($this->checkEndTranslate($uuid)){
        //     $m_translate->endTranslate($id, filesize($target_storage_path));
        // }else{
        //     $m_translate->failedTranslate($id, $cmd);
        // }

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
                    if($values[0]==-1){
                        check(false, $values[1]);
                    }else{
                        $process=intval($values[1])/intval($values[0]);
                    }
                }else if(count($values)>2){
                    $process=intval($values[1])/intval($values[0]);
                    $count=$values[2];
                    $spend=$values[3];
                    $m_translate=new Translate();
                    $translate=$m_translate->getTranslateInfoByUUID($uuid);
                    if(!empty($translate)){
                        $url='/storage/'.trim($translate['target_filepath'],'/');
                        if(strtolower($translate['status'])!='done'){
                            $target_path=storage_path('app/public'.$translate['target_filepath']);
                            $m_translate->endTranslate($translate['id'], filesize($target_path));
                        }
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
