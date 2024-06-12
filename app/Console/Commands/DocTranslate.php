<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Translate;
use App\Models\Customer;

class DocTranslate extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doc:translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '文档翻译';

    /**
     * Execute the console command.
     */
    public function handle(){

        date_default_timezone_set('Asia/Shanghai');

        $m_translate=new Translate();

        $translates = Translate::where(function($q){
                $q->where('status', 'none')->orWhere(function($t){
                    $t->where('status','process')
                        ->where('start_at','<', date('Y-m-d H:i:s',strtotime('+1 minutes')));
                });
            })
            ->where('deleted_flag', 'N')
            ->orderBy('id','asc')
            ->get()->toArray();
        
        if(empty($translates)) return [];

        $ids=[];
        
        foreach($translates as $t){

            $translate_id=$t['id'];
            $uuid=$t['uuid'];
            $lang=$t['lang'];
            $model=$t['model'];
            $threads=$t['threads'];
            $api_url=$t['api_url'] ?? '';
            $api_key=$t['api_key'] ?? '';

            $translate_main=base_path('python/translate/main.py');
            $origin_filepath=storage_path('app/public/'.$t['origin_filepath']);

            $target_file=storage_path('app/public'.$t['target_filepath']);
            $target_dir=pathinfo($target_file, PATHINFO_DIRNAME);
            @mkdir($target_dir);
            $target_url='/storage'.$t['target_filepath'];
            $process_file=storage_path('app/public/process/'.$uuid.'.txt');
           
            $prompt=str_replace('{target_lang}', $lang, $t['prompt']);

            $customer=Customer::where('id', $t['customer_id'])->first();
            if(!empty($customer) && strtolower($customer['level'])=='vip'){
                $api_url=config('openai.api_url');
                $api_key=config('openai.api_key');
            }

            $m_translate->startTranslate($translate_id);
            echo "python3 $translate_main -f $origin_filepath -o $target_file -l $lang --model $model --system '$prompt' --threads $threads --processfile $process_file --api_url $api_url --api_key $api_key --output_url '$target_url'";
            // exit;
            $cmd = shell_exec("python3 $translate_main -f $origin_filepath -o $target_file -l $lang --model $model --system '$prompt' --threads $threads --processfile $process_file --api_url $api_url --api_key $api_key --output_url '$target_url'");
            echo $cmd;
            if($this->checkEndTranslate($uuid)){
                $m_translate->endTranslate($translate_id, filesize($target_file));
            }
        }
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
