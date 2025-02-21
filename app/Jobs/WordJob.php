<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Jobs;

use Exception;
use Illuminate\Support\Facades\Log;

class WordJob extends Job {

    protected $response;
    protected $uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uuid) {
        $this->uuid = $uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            date_default_timezone_set('Asia/Shanghai');
            $translate_main = base_path('python/translate/main.py');
            $storage_path = storage_path('app/public');
            shell_exec('python3 ' . $translate_main . ' ' . $this->uuid . ' ' . $storage_path . ' 2>&1');
        } catch (Exception $ex) {
            Log::channel('command')->info('python3:' . $ex->getMessage());
        }
    }

}
