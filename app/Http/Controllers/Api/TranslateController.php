<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Translate;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use ZipArchive;

/**
 * 翻译记录
 */
class TranslateController extends BaseAuthController {

    protected $skip_methods = ['setting', 'test', 'finishTotal', 'check_openai', 'openai'];

    public function getMessages() {
        return [
            'start' => [
                'server.required' => Lang::get('translate.server_required'),
                'server.in' => Lang::get('translate.server_required'),
                'api_url.required_if' => Lang::get('translate.api_url_required'),
                'api_key.required_if' => Lang::get('translate.api_key_required'),
                'model.required' => Lang::get('translate.model_required'),
                'lang.required' => Lang::get('translate.lang_required'),
                'type.required' => Lang::get('translate.type_required'),
                'uuid.required' => Lang::get('translate.uuid_required'),
                'prompt.required' => Lang::get('translate.prompt_required'),
                'threads.required' => Lang::get('translate.threads_required'),
                'file_name.required' => Lang::get('translate.file_name_required'),
                'file_path.required' => Lang::get('translate.file_path_required'),
                'origin_lang.required_if' => Lang::get('translate.origin_lang_required'),
            ],
            'process' => [
                'uuid.required' => Lang::get('translate.uuid_required')
            ],
            'check_openai' => [
                'server.required' => Lang::get('translate.server_required'),
                'server.in' => Lang::get('translate.server_required'),
                'api_url.required_if' => Lang::get('translate.api_url_required'),
                'api_key.required_if' => Lang::get('translate.api_key_required'),
                'model.required' => Lang::get('translate.model_required'),
            ],
            'check_pdf' => [
                'file_path.required' => Lang::get('translate.file_path_required'),
            ],
            'check_doc2x' => [
                'doc2x_secret_key.required' => Lang::get('translate.doc2x_secret_key'),
            ],
        ];
    }

    public function getRules() {
        return [
            'start' => [
                'server' => 'required|in:openai,member',
                'api_url' => 'required_if:server,openai',
                'api_key' => 'required_if:server,openai',
                'model' => 'required',
                'lang' => 'required',
                'type' => 'required',
                'uuid' => 'required',
                'prompt' => 'required',
                'threads' => 'required',
                'file_path' => 'required',
                'file_name' => 'required',
                'origin_lang' => 'required_if:scanned,1', // 当 scanned 为 true 时,origin_lang 是必需的
            ],
            'process' => [
                'uuid' => 'required',
            ],
            'check_openai' => [
                'server' => 'required|in:openai,member',
                'api_url' => 'required_if:server,openai',
                'api_key' => 'required_if:server,openai',
                'model' => 'required',
            ],
            'check_pdf' => [
                'file_path' => 'required',
            ],
            'check_doc2x' => [
                'doc2x_secret_key' => 'required',
            ],
        ];
    }

    public function index(Request $request) {
        $params = $request->input();
        $m_translate = new Translate();
        $params['customer_id'] = $this->customer_id;
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $data = $m_translate->getTranslates($params, $page, $limit);
        ok($data);
    }

    public function setting() {
        $m_setting = new Setting();
        $api_setting = $m_setting->getSettingByGroup('api_setting');
        $other_setting = $m_setting->getSettingByGroup('other_setting');
        $api_setting['models'] = explode(',', $api_setting['models'] ?? '');
        unset($api_setting['api_url']);
        unset($api_setting['api_key']);
        return ok(array_merge($api_setting, $other_setting));
    }

    /**
     * 关联客户列表
     * @param  Request $request
     * @return
     */
    public function start(Request $request) {
        @set_time_limit(3600);
        date_default_timezone_set('Asia/Shanghai');
        $params = $request->post();
        $this->validate($params, 'start');
        $translate_main = base_path('python/translate/main.py');
        $storage_path = storage_path('app/public');
        $origin_filepath = $params['file_path'];
        $md5 = md5(file_get_contents($storage_path . $origin_filepath));
        $translate = Translate::where('md5', $md5)
                ->where('status', 'done')
                ->where('deleted_flag', 'N')
                ->where('lang', $params['lang'])
                ->where('model', $params['model'])
                ->where('prompt', $params['prompt'])
                ->where('api_url', $params['api_url'])
                ->where('api_key', $params['api_key'])
                ->first();
        $upload_filename = mb_convert_encoding($params['file_name'], 'UTF-8', 'auto');
        $uuid = $params['uuid'];
        if (!empty($translate) && $translate['uuid'] == $uuid) {
            ok($translate);
        }

        $extension = pathinfo($upload_filename, PATHINFO_EXTENSION);
        $filename = pathinfo($upload_filename, PATHINFO_FILENAME);

        $target_filepath = sprintf('/translate/%s/%s', basename(pathinfo($origin_filepath, PATHINFO_FILENAME), $extension), $filename . '-' . $params['lang'] . '.' . $extension);
        $origin_storage_path = $storage_path . $origin_filepath;
        $target_storage_path = $storage_path . $target_filepath;
        $target_dir = pathinfo($target_storage_path, PATHINFO_DIRNAME);
        @mkdir($target_dir);
        $lang = $params['lang'];
        $model = $params['model'];
        $type = (!empty($params['type']) && is_array($params['type'])) ? array_pop($params['type']) : '';
        $backup_model = $params['backup_model'] ?? '';
        $prompt = str_replace('{target_lang}', $lang, $params['prompt']);
        $threads = $params['threads'];
        $api_url = $params['api_url'] ?? '';
        $api_key = $params['api_key'] ?? '';
        $doc2x_flag = $params['doc2x_flag'] ?? 'N';
        $doc2x_secret_key = $params['doc2x_secret_key'] ?? '';
        $m_setting = new Setting();
        if ($params['server'] == 'member') {
            $api_setting = $m_setting->getSettingByGroup('api_setting');
            if (!empty($api_setting)) {
                $api_url = $api_setting['api_url'];
                $api_key = $api_setting['api_key'];
            } else {
                $api_url = config('openai.api_url');
                $api_key = config('openai.api_key');
            }
        }

        $prompt_id = $params['prompt_id'] ?? 0;
        $comparison_id = $params['comparison_id'] ?? 0;
        $m_translate = new Translate();
        $id = $m_translate->addTranslate([
            'origin_filename' => $upload_filename,
            'origin_filepath' => $origin_filepath,
            'target_filepath' => $target_filepath,
            'origin_filesize' => filesize($origin_storage_path),
            'customer_id' => $this->customer_id,
            'uuid' => $uuid,
            'lang' => $lang,
            'md5' => $md5,
            'origin_lang' => $params['origin_lang'] ?? '', // 添加 origin_lang
            'model' => $model,
            'type' => $type,
            'backup_model' => $backup_model,
            'comparison_id' => intval($comparison_id),
            'prompt_id' => intval($prompt_id),
            'prompt' => $prompt,
            'api_url' => $api_url,
            'api_key' => $api_key,
            'threads' => $threads,
            'doc2x_flag' => $doc2x_flag,
            'doc2x_secret_key' => $doc2x_secret_key,
        ]);
        ignore_user_abort(true);
        $m_translate->startTranslate($id);
        $cmd = shell_exec("python3 $translate_main $uuid $storage_path  2>&1");
        echo $cmd;
    }

    public function process(Request $request) {
        date_default_timezone_set('Asia/Shanghai');
        $params = $request->post();
        $this->validate($params, 'process');
        $uuid = $params['uuid'];
        $m_translate = new Translate();
        $translate = $m_translate->getTranslateInfoByUUID($uuid);
        if (!empty($translate)) {
            $url = '/storage/' . trim($translate['target_filepath'], '/');
            check($translate['status'] != 'failed', $translate['failed_reason']);
            ok([
                'process' => $translate['process'],
                'url' => $translate['process'] == 100 ? $url : '',
                'count' => $translate['word_count'],
                'time' => spend_time($translate['start_at'], $translate['end_at']),
                'end_time' => $translate['end_at']
            ]);
        }
        check(false, Lang::get('translate.translate_not_found'));
    }

    /**
     * 判断openai接口是否正常运行
     * @param  Request $request
     * @return
     */
    public function check_openai(Request $request) {
        $params = $request->post();
        $this->validate($params, 'check_openai');
        $api_url = $params['api_url'] ?? '';
        $api_key = $params['api_key'] ?? '';
        $m_setting = new Setting();
        if ($params['server'] == 'member') {
            $api_setting = $m_setting->getSettingByGroup('api_setting');
            if (!empty($api_setting)) {
                $api_url = $api_setting['api_url'];
                $api_key = $api_setting['api_key'];
            } else {
                $api_url = config('openai.api_url');
                $api_key = config('openai.api_key');
            }
        }
        $model = $params['model'];
        $check_main = base_path('python/translate/check_openai.py 2>&1');
        $result = shell_exec("python3 $check_main '$api_url' '$api_key' '$model'");
        if (trim($result) == 'OK') {
            ok('成功');
        } else {
            check(false, $result);
        }
    }

    /**
     * 判断pdf是否是扫描件
     * @param  Request $request
     * @return
     */
    public function check_pdf(Request $request) {
        $params = $request->post();
        $this->validate($params, 'check_pdf');
        $file_path = $params['file_path'];
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if (strtolower($extension) != 'pdf') {
            ok(['scanned' => 0]);
        }
        $storage_path = storage_path('app/public');
        $pdf_path = $storage_path . $file_path;
        $check_main = base_path('python/translate/check_pdf.py');
        // echo "python3 $check_main '$pdf_path'";
        $result = shell_exec("python3 -u $check_main '$pdf_path' 2>&1");
        check(trim($result) == 'True' || trim($result) == 'False', $result);
        if (trim($result) == 'True') {
            ok(['scanned' => 1]);
        } else {
            ok(['scanned' => 0]);
        }
    }

    /**
     * 判断pdf是否是扫描件
     * @param  Request $request 
     * @return 
     */
    public function check_doc2x(Request $request) {
        $params = $request->post();
        $this->validate($params, 'check_doc2x');
        $doc2x_secret_key = $params['doc2x_secret_key'];
        $check_main = base_path('python/translate/check_doc2x.py');
        print_r("python3 -u $check_main $doc2x_secret_key");
        $result = shell_exec("python3 -u $check_main $doc2x_secret_key");
        print_r($result);
        check(intval($result) == 200, 'failed');
        ok('ok');
    }

    public function del(Request $request, $id) {
        $m_translate = new Translate();
        $m_translate->deleteCustomerTranslate($this->customer_id, $id);
        ok();
    }

    public function delAll(Request $request) {
        $m_translate = new Translate();
        $m_translate->deleteAllTranslate($this->customer_id);
        ok();
    }

    private function checkEndTranslate($uuid) {
        $file = storage_path('app/public/process/' . $uuid . '.txt');
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (!empty($content)) {
                $values = explode('$$$', $content);
                if (count($values) > 2) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 已完成翻译的总量
     * @param  Request $request
     * @return
     */
    public function finishTotal(Request $request) {
        $m_translate = new Translate();
        $total = $m_translate->getFinishTotal();
        ok(['total' => $total]);
    }

    /**
     * 下载全部
     * @param  Request $request
     * @return
     */
    public function downloadAll(Request $request) {
        $m_translate = new Translate();
        $params['customer_id'] = $this->customer_id;
        if (empty($this->customer_id)) {
            die('没有文件可下载');
        }
        $params['status'] = 'done';
        $results = $m_translate->getTranslates($params, 1, 100);
        if (empty($results['data'])) {
            die('没有文件可下载');
        }

        $zip = new ZipArchive();
        $zipFileName = '/tmp/files-' . date('Ymd') . rand(100000, 999999) . '.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("无法打开 <$zipFileName>");
        }
        $storage_path = storage_path('app/public/');
        // 将文件添加到 ZIP
        foreach ($results['data'] as $res) {
            $fullpath = $storage_path . trim($res->target_filepath_copy, '/');
            if (file_exists($fullpath)) {
                $zip->addFile($fullpath, basename($fullpath)); // 添加文件到 ZIP 中
            } else {
                
            }
        }
        $zip->close();
        if (!file_exists($zipFileName)) {
            die('打包失败');
        }
        // 设置头信息进行下载
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Content-Length: ' . filesize($zipFileName));

        // 读取文件内容并输出
        readfile($zipFileName);
        // 删除生成的 ZIP 文件
        @unlink($zipFileName);
    }

    public function test(Request $request) {
        $translate_main = base_path('python/translate/main.py');
        $test_main = base_path('python/translate/test.py');
        $uuid = "bb451d18e80fa731d1b43fbbe79cce76d4c542f4";
        $storage_path = storage_path('app/public');
        $cmd = shell_exec("python3 $translate_main $uuid $storage_path");
        echo $cmd;
        // $result = shell_exec("python3 $test_main");
        // echo $result;
    }

}
