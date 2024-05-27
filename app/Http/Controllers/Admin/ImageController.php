<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Typography\FontFactory;


/**
 * 图片
 */
class ImageController extends BaseController {

    /**
     * 列表
     */
    public function index(Request $request) {
        $image = ImageManager::gd()->read(base_path('public/static/img/rsic.jpeg'));
        $width = $image->width();
        $height = $image->height();

        $image->text('The quick brown fox', $width-120, $height-20,function(FontFactory $font){
            $font->size(20);
        });
        $image->save(base_path('/public/static/img/rsic2.png'));
        ok([]);
    }
}
