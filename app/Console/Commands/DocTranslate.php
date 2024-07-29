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

        @set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');

        $m_translate=new Translate();

        $translates = Translate::where(function($q){
                $q->where('status', 'none')->orWhere(function($t){
                    $t->where('status','process')
                        ->where('start_at','<', date('Y-m-d H:i:s',strtotime('+1 minutes')));
                })
                ->orWhere(function($t){
                    $t->where('status','failed')
                        ->where('failed_count','<', 3);
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

            $translate_main=base_path('python/translate/main.py');

            $target_file=storage_path('app/public'.$t['target_filepath']);
            $target_dir=pathinfo($target_file, PATHINFO_DIRNAME);
            @mkdir($target_dir);

            $storage_path=storage_path('app/public');

            $m_translate->startTranslate($translate_id);
            $cmd = shell_exec("python3 $translate_main $uuid $storage_path");
            echo $cmd;
            if($this->checkEndTranslate($uuid)){
                // $m_translate->endTranslate($translate_id, filesize($target_file));
            }else{
                // $m_translate->failedTranslate($translate_id, $cmd);
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
