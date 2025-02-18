<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Comparison;
use App\Models\Setting;
use Illuminate\Support\Facades\Lang;
use PhpOffice\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * 翻译对照表
 */
class ComparisonController extends BaseAuthController {

    protected $skip_methods=['template'];

    public function __construct(){
        parent::__construct();
        if(in_array($this->current_method, $this->skip_methods)){
            return;
        }
        check(!empty($this->customer_id), Lang::get('comparison.need_business_version'));
    }

    public function getMessages(){
        return [
            'add'=>[
                'title.required'=>Lang::get('comparison.title_required'),
                'origin_lang.required'=>Lang::get('comparison.origin_lang_required'),
                'target_lang.required'=>Lang::get('comparison.target_lang_required'),
                'content.required'=>Lang::get('comparison.content_required'),
                'content.array'=>Lang::get('comparison.content_array'),
            ],
            'edit'=>[
                'title.required'=>Lang::get('comparison.title_required'),
                'origin_lang.required'=>Lang::get('comparison.origin_lang_required'),
                'target_lang.required'=>Lang::get('comparison.target_lang_required'),
                'content.required'=>Lang::get('comparison.content_required'),
                'content.array'=>Lang::get('comparison.content_array'),
            ],
            'edit_share'=>[
                'share_flag.required'=>Lang::get('comparison.share_flag_required'),
                'share_flag.in'=>Lang::get('comparison.share_flag_in'),
            ],
            'import'=>[
                'file.required'=>Lang::get('comparison.import_file_required'),
                'file.file'=>Lang::get('comparison.import_file_need'),
                'file.mimes'=>Lang::get('comparison.import_file_excel'),
            ]
        ];
    }

    public function getRules(){
        return [
            'add'=>[
                'title'=>'required',
                'origin_lang'=>'required',
                'target_lang'=>'required',
                'content'=>'required|array',
                'share_flag'=>'in:Y,N',
            ],
            'edit'=>[
                'title'=>'required',
                'origin_lang'=>'required',
                'target_lang'=>'required',
                'content'=>'required|array',
                'share_flag'=>'in:Y,N',
            ],
            'edit_share'=>[
                'share_flag'=>'required|in:Y,N',
            ],
            'import'=>[
                'file'=>'required|file|mimes:xlsx,xls',
            ]
        ];
    }


    public function my(Request $request){
        $params=$request->input();
        $m_comparison=new Comparison();
        $params['customer_id']=$this->customer_id;
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 10;
        $data=$m_comparison->getMyComparisons($params, $page, $limit);
        ok($data);
    }

    /**
     * 广场列表
     * @param  Request $request 
     */
    public function share(Request $request){
        $params=$request->input();
        $m_comparison=new Comparison();
        $params['customer_id']=$this->customer_id;
        $page=$params['page'] ?? 1;
        $limit=$params['limit'] ?? 10;
        $data=$m_comparison->getSharedComparisons($params, $page, $limit);
        ok($data);
    }

    /**
     * 添加对照数据
     */
    public function add(Request $request){
        $params=$request->post();
        $this->validate($params, 'add');
        $m_comparison=new Comparison();
        $params['customer_id']=$this->customer_id;
        $m_comparison->addComparion($params);
        ok();
    }

    public function edit(Request $request, $comparison_id){
        $params=$request->post();
        $this->validate($params, 'edit');
        $m_comparison=new Comparison();
        $params['customer_id']=$this->customer_id;
        $m_comparison->editComparion($comparison_id,$params);
        ok();
    }

    /**
     * 更新分享状态
     * @param  Request $request 
     */
    public function edit_share(Request $request,$comparison_id){
        $params=$request->post();
        $this->validate($params, 'edit_share');
        $m_comparison=new Comparison();
        $share_flag=$params['share_flag'];
        $m_comparison->editComparionShare($this->customer_id, $comparison_id, $share_flag);
        ok();
    }

    /**
     * 删除
     * @param  Request $request 
     */
    public function del(Request $request,$comparison_id){
        $params=$request->post();
        $m_comparison=new Comparison();
        $m_comparison->delComparion($this->customer_id, $comparison_id);
        ok();
    }

    /**
     * 加入我的语料库
     * @param  Request $request 
     */
    public function copy(Request $request,$comparison_id){
        $params=$request->post();
        $m_comparison=new Comparison();
        $m_comparison->copyComparion($this->customer_id, $comparison_id);
        ok();
    }

    /**
     * 收藏或取消收藏 
     * @param  Request $request 
     */
    public function fav(Request $request,$comparison_id){
        $params=$request->post();
        $m_comparison=new Comparison();
        $m_comparison->favComparion($this->customer_id, $comparison_id);
        ok();
    }

    public function template(Request $request){
        // 定义数组
        $languages = ['中文', '英语', '日语', '俄语', '阿拉伯语', '西班牙语'];

        // 创建新的 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置单元格 A1 和 A2 为下拉选择框
        $sheet->getCell('A1')->setDataValidation(
            (new \PhpOffice\PhpSpreadsheet\Cell\DataValidation())
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setFormula1('"' . implode(',', $languages) . '"')
                ->setShowDropDown(true)
        );

        $sheet->getCell('B2')->setDataValidation(
            (new \PhpOffice\PhpSpreadsheet\Cell\DataValidation())
                ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setFormula1('"' . implode(',', $languages) . '"')
                ->setShowDropDown(true)
        );
        $sheet->setTitle('术语对照表默认标题');
        // 设置文件名
        $fileName = '术语表模板.xlsx';

        // 保存到 storage/app/public 目录
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $fileName);
        $writer->save($filePath);

        // 返回下载响应
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * 导入数据
     */
    public function import(Request $request){
        // 验证上传的文件
        $params=$request->post();
        $file=$request->file();
        $this->validate(array_merge($params, $file), 'import');

        // 读取用户上传的文件
        $spreadsheet = IOFactory::load($file['file']->getRealPath());
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            // 获取第一行的源语言和目标语言
            $origin_lang = $sheet->getCell('A1')->getValue();
            $target_lang = $sheet->getCell('B1')->getValue();

            // 检查每个单元格值是否为空
            check(!empty($origin_lang) && !empty($target_lang), Lang::get('comparison.lang_required'));
            $title = $sheet->getTitle(); 
            // 处理后面的行数据
            $contents = [];
            $i=0;
            foreach ($sheet->getRowIterator(2) as $row) { // 从第二行开始
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // 包括空单元格

                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    check(!empty($value), Lang::get('comparison.item_required'));
                    $contents[$i][] = $value;
                }
                $i++;
            }

            $m_comparison=new Comparison();
            $m_comparison->addComparion([
                'title'=>$title,
                'origin_lang'=>$origin_lang,
                'target_lang'=>$target_lang,
                'customer_id'=>$this->customer_id,
                'content'=>$contents,
                'share_flag'=>'N'
            ]);
        }
        ok();
    }

    /**
     * 导出数据
     */
    public function export(Request $request, $comparison_id){
        $m_comparison=new Comparison();
        $comparison=$m_comparison->getCustomerComparion($comparison_id,$this->customer_id);
        if(empty($comparison)){
            exit('对照表不存在');
        }
        $contents=[];
        $items=explode(';', $comparison['content']);
        foreach($items as $item){
            $contents[]=explode(',',$item);
        }
        // 创建新的 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($comparison['title']);
        $sheet->setCellValue('A1', $comparison['origin_lang']);
        $sheet->setCellValue('B1', $comparison['target_lang']);
        // 从 A1 开始写入数据
        $row = 2; // 从第一行开始
        $alphas=range('A', 'Z');
        foreach ($contents as $item) {
            $col = 0; // 从第一列开始
            foreach ($item as $value) {
                $sheet->setCellValue($alphas[$col] . $row, $value);
                $col++;
            }
            $row++;
        }

        // 设置文件名
        $fileName = $comparison['title'].'.xlsx';

        // 保存到 storage/app/public 目录
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $fileName);
        $writer->save($filePath);

        // 返回下载响应
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * 导出数据
     */
    public function exportAll(Request $request){
        $m_comparison=new Comparison();
        $comparisons=$m_comparison->getCustomerComparions($this->customer_id);
        if(empty($comparisons)){
            exit('对照表不存在');
        }
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        foreach($comparisons as $index => $comparison){
            $sheet = $spreadsheet->createSheet($index);
            $contents=[];
            $items=explode(';', $comparison['content']);
            foreach($items as $item){
                $contents[]=explode(',',$item);
            }
            // 创建新的 Spreadsheet 对象
            $sheet->setTitle(mb_substr($comparison['title'], 0, mb_strlen($comparison['title'])>31?31: mb_strlen($comparison['title'])));
            $sheet->setCellValue('A1', $comparison['origin_lang']);
            $sheet->setCellValue('B1', $comparison['target_lang']);
            // 从 A1 开始写入数据
            $row = 2; // 从第一行开始
            $alphas=range('A', 'Z');
            foreach ($contents as $item) {
                $col = 0; // 从第一列开始
                foreach ($item as $value) {
                    $sheet->setCellValue($alphas[$col] . $row, $value);
                    $col++;
                }
                $row++;
            }
        }

        // 设置文件名
        $fileName ='所有对照表数据.xlsx';

        // 保存到 storage/app/public 目录
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $fileName);
        $writer->save($filePath);

        // 返回下载响应
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
