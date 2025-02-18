<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Jobs;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Helpers\Translate;

class WordJob extends Job {

    protected $response;
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request) {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            date_default_timezone_set('Asia/Shanghai');
            $repo = new GrabInquiryRepo();
            $repo->import($this->request);
        } catch (Exception $ex) {
            Log::channel('command')->info('GrabInquiryJob:' . $ex->getMessage());
        }
    }

}
