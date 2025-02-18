<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Description of 简易运
 *
 * @author zyg
 */
class Translate {

    private $apiKey = '';

    /**
     * @echo 获取手机验证码 该手机验证码有效期为15分钟。
     * @method POST
     */
    private $apiUrl = '';

    /**
     *
     * @var string
     */
    const METHON_GET = 'GET';
    const METHON_POST = 'POST';
    const METHON_DELETE = 'DELETE';

    public function __construct($apiKey, $apiUrl) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function translate(array $trans, array $texts, $index) {
        if (empty($texts)) {
            return '';
        }
        $translateId = $trans['id'];
        $targetLang = $trans['lang'];
        $model = $trans['model'];
        $backupModel = $trans['backup_model'];
        $prompt = $trans['prompt'];
        $extension = $trans['extension'];
        $text = $texts[$index];
        try {
            if ($text['complete'] === false) {
                if ($extension == ".pdf") {
                    if ($text['type'] == "text") {
                        $content = $this->translateHtml($text['text'], $targetLang, $model, $prompt);
                    } else {
                        $content = $this->getContentByImage($text['text'], $targetLang);
                    }
                } else {
                    $content = req(text['text'], target_lang, model, prompt);
                }
                $text['count'] = $countText($text['text']);
                if ($this->checkTranslated($content)) {
                    $text['text'] = $content;
                }
                $text['complete'] = true;
            }
        } catch (\Exception $ex) {
            switch ($ex->getCode()) {
                case '400':
                    check(false, '请求无法与openai服务器建立安全连接');
                    break;
                case '401':
                    check(false, 'openai密钥或令牌无效');
                    break;
                case '429':
                    check(false, '访问速率达到限制,10分钟后再试');
                    break;
                default :
                    check(false, $ex->getMessage());
                    break;
            }
        }
    }

    public function post($data, $timeChat) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl . '/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ),
        ));
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            Log::channel('command')->info('ONECHAT: ' . $error);
            curl_close($curl);
            check(false, $error);
        }
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode < 200 && $httpCode >= 300) {
            Redis::set($timeChat, 21, 60);
            Log::channel('command')->info('ONECHAT: ' . $httpCode);
            check(false, '429 (Too Many Requests)');
        }
        $response = curl_exec($curl);
        Log::channel('command')->info('ONECHAT: ' . $response);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function error($message, $stream) {
        $ret = json_encode(['error' => [
                "message" => $message,
                'type' => '',
                "param" => '',
                "code" => "401"
            ]
                ], 256);
        if ($stream) {
            return 'data: ' . $ret . "\r\n\r\n" . 'data: [DONE]' . "\r\n\r\n";
        }
        die($ret);
    }

    public function messageParse($text) {
        $dataList = explode("\r\n", $text);
        $message = '';
        foreach ($dataList as $item) {
            $json = json_decode(str_replace('data: ', '', $item), true);
            if ($json === ['DONE']) {
                return $message;
            }
            if (!empty($json['finish_reason'])) {
                $message['choices'][0]['finish_reason'] = $json['finish_reason'];
                return $message;
            }
            if (empty($json['choices'])) {
                continue;
            }
            if (empty($json['choices'][0])) {
                continue;
            }
            if (empty($json['choices'][0]['delta'])) {
                continue;
            }
            if (!empty($json['choices'][0]['delta']['content'])) {
                $message .= $json['choices'][0]['delta']['content'];
            }
        }
        return $message;
    }

}
