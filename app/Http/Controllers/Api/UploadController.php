<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class UploadController extends BaseAuthController {

    const ALLOW_EXTENSIONS = ['docx', 'xlsx', 'pptx', 'pdf', 'txt', 'md', 'csv', 'xls', 'doc'];

    public function getMessages() {
        return [
            'del' => [
                'filepath.required' => Lang::get('upload.filepath_required')
            ],
            'upload' => [
                'file.required' => Lang::get('upload.file_required'),
                'file.file' => Lang::get('upload.file_required'),
                'file.mimes' => Lang::get('upload.file_mimes'),
                'file.extensions' => Lang::get('upload.file_mimes'),
            ]
        ];
    }

    public function getRules() {
        return [
            'del' => [
                'filepath' => 'required'
            ],
            'upload' => [
                'file' => 'required|file|mimes:' . implode(',', self::ALLOW_EXTENSIONS) . '|extensions:' . implode(',', self::ALLOW_EXTENSIONS)
            ]
        ];
    }

    /**
     * 
     */
    public function index(Request $request) {
        $params = $request->post();
        $file = $request->file('file');
        $params['file'] = $file;
        $this->validate($params, 'upload');

        $ext = $file->getClientOriginalExtension();
        $hash = $file->hashName() ? $file->hashName() : Str::random(40);
        $extension = substr($hash, strripos($hash, '.') + 1);
        check(in_array($extension, self::ALLOW_EXTENSIONS), Lang::get('upload.file_mimes'));
        if ($file->isValid()) {
            $filesize = filesize($file->getPathname());
            if ($this->customer_id != 0) {
                $m_customer = new Customer();
                $avaiableStorage = $m_customer->getCustomerAvaiableStorage($this->customer_id);
                check($avaiableStorage > $filesize, '存储空间不足');
            }

            $datetime = date('ymd');
            $filename = explode('.', $hash)[0];
            $path = $file->storeAs('/uploads/' . $datetime, $filename . '.' . $ext);
            $uuid = create_uuid();
            ok([
                'filepath' => '/' . $path,
                'filename' => $file->getClientOriginalName(),
                'uuid' => $uuid
            ]);
        }

        check(false, '文件上传失败');
    }

    public function del(Request $request) {
        $params = $request->post();
        $this->validate($params, 'del');

        $filepath = $params['filepath'];
        $fullpath = storage_path('app/public' . $filepath);
        if (file_exists($fullpath)) {
            unlink($fullpath);
        }

        ok();
    }

}
