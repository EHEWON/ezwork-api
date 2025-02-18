<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\ComparisonFav;

/**
 * 翻译对照表
 */
class Comparison extends Model {

    protected $table = "comparison";

    /**
     * 获取我的对照表
     * @param  array  $params 
     * @param  int $page   
     * @param  int $limit  
     */
    public function getMyComparisons($params, $page = 1, $limit = 20) {
        $query = DB::table($this->table, 'c')->where('c.deleted_flag', 'N');
        if (empty($params['customer_id'])) {
            return ['data' => [], 'total' => 0];
        }
        $query->where('customer_id', $params['customer_id']);
        if (!empty($params['keyword'])) {
            $query->where(function($q) use($params) {
                $keyword = '%' . $params['keyword'] . '%';
                $q->where('c.title', 'like', $keyword);
            });
        }

        $total = $query->clone()->count();
        $query->selectRaw('c.id,c.title,c.origin_lang,c.target_lang,c.content,c.share_flag,c.created_at');
        $query->skip(($page - 1) * $limit)->limit($limit);
        $results = $query->orderBy('id', 'desc')->get()->toArray();
        foreach ($results as &$result) {
            $items = [];
            $contents = explode(';', $result->content);
            foreach ($contents as $index => $item) {
                $_items = explode(',', $item);
                $items[$index]['origin'] = $_items[0];
                $items[$index]['target'] = $_items[1];
            }
            $result->content = $items;
        }
        return ['data' => $results, 'total' => $total];
    }

    /**
     * 获取广场分享的对照表
     * @param  array  $params 
     * @param  int $page   
     * @param  int $limit  
     */
    public function getSharedComparisons($params, $page = 1, $limit = 20) {
        $query = DB::table($this->table, 'c')->where('c.deleted_flag', 'N');
        if (empty($params['customer_id'])) {
            return ['data' => [], 'total' => 0];
        }
        $query->whereNot('c.customer_id', $params['customer_id'])->where('share_flag', 'Y');
        if (!empty($params['keyword'])) {
            $query->where(function($q) use($params) {
                $keyword = '%' . $params['keyword'] . '%';
                $q->where('c.title', 'like', $keyword);
            });
        }

        $total = $query->clone()->count();
        $query->selectRaw('c.id,c.title,c.origin_lang,c.target_lang,c.content,c.share_flag,c.created_at,c.added_count,IF(ISNULL(f.comparison_id),0,1) faved,cc.email');
//        $query->leftJoin('comparison_fav as f', 'c.id', '=', 'f.comparison_id');
        $query->leftJoin('comparison_fav as f', function($join) use ($params) {
            $join->on('c.id', '=', 'f.comparison_id')
                    ->where('f.customer_id', '=', $params['customer_id']);
        });
        $query->leftJoin('customer as cc', 'cc.id', '=', 'c.customer_id');

        if (!empty($params['order'])) {
            switch ($params['order']) {
                case 'latest':
                    $query->orderBy('c.id', 'desc');
                    break;
                case 'fav':
                    $query->orderBy('faved', 'desc')
                            ->orderBy('c.id', 'desc');
                    break;
                case 'added':
                    $query->orderBy('c.added_count', 'desc')
                            ->orderBy('c.id', 'desc');
                    break;
                default :
                    $query->orderBy('c.id', 'desc');
                    break;
            }
        }

        $query->skip(($page - 1) * $limit)->limit($limit);
        $results = $query->orderBy('id', 'desc')->get()->toArray();
        foreach ($results as &$result) {
            $items = [];
            $contents = explode(';', $result->content);
            foreach ($contents as $index => $item) {
                $_items = explode(',', $item);
                $items[$index]['origin'] = $_items[0];
                $items[$index]['target'] = $_items[1];
            }
            $result->content = $items;
            $result->email = email_hidden($result->email);
        }
        return ['data' => $results, 'total' => $total];
    }

    /**
     * 获取对照数据 
     */
    public function getComparion($comparison_id) {
        return $this->where('id', $comparison_id)->where('deleted_flag', 'N')->first();
    }

    /**
     * 获取用户的对照数据 
     */
    public function getCustomerComparion($comparison_id, $customer_id) {
        return $this->where('id', $comparison_id)->where('customer_id', $customer_id)->where('deleted_flag', 'N')->first();
    }

    /**
     * 获取用户的所有对照数据 
     */
    public function getCustomerComparions($customer_id) {
        return $this->where('customer_id', $customer_id)->where('deleted_flag', 'N')->get()->toArray();
    }

    /**
     * 添加翻译对照数据
     */
    public function addComparion($params) {
        return $this->insertGetId([
                    'customer_id' => $params['customer_id'],
                    'title' => $params['title'],
                    'origin_lang' => $params['origin_lang'],
                    'target_lang' => $params['target_lang'],
                    'share_flag' => $params['share_flag'] ?? 'N',
                    'content' => $this->get_content($params['content']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 编辑翻译对照数据
     */
    public function editComparion($comparison_id, $params) {
        $customer_id = $params['customer_id'];
        $exists = $this->check_title_exists($customer_id, $comparison_id, $params['title']);
        check(!$exists, Lang::get('comparion.title_exists'));
        $info = $this->where('customer_id', $customer_id)->where('id', $comparison_id)->first();
        check(!empty($info), Lang::get('comparion.comparion_not_exists'));
        return $this->where('id', $comparison_id)->update([
                    'title' => $params['title'],
                    'customer_id' => $params['customer_id'],
                    'origin_lang' => $params['origin_lang'],
                    'target_lang' => $params['target_lang'],
                    'share_flag' => $params['share_flag'] ?? 'N',
                    'content' => $this->get_content($params['content']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 编辑分享状态
     */
    public function editComparionShare($customer_id, $comparison_id, $share_flag) {
        $info = $this->where('customer_id', $customer_id)->where('id', $comparison_id)->first();
        check(!empty($info), Lang::get('comparion.comparion_not_exists'));
        $this->where('id', $comparison_id)->update(['share_flag' => $share_flag]);
    }

    /**
     * 删除对照表
     */
    public function delComparion($customer_id, $comparison_id) {
        $info = $this->where('customer_id', $customer_id)->where('id', $comparison_id)->first();
        check(!empty($info), Lang::get('comparion.comparion_not_exists'));
        $this->where('id', $comparison_id)->update(['deleted_flag' => 'Y']);
    }

    /**
     * 复制对照表
     */
    public function copyComparion($customer_id, $comparison_id) {
        $info = $this->where('id', $comparison_id)->first();
        check(!empty($info), Lang::get('comparion.comparion_not_exists'));
        check($info['customer_id'] != $customer_id, Lang::get('comparion.comparion_canot_copy'));
        $exists = $this->check_title_exists($customer_id, 0, $info['title']);
        check(!$exists, Lang::get('comparion.comparion_copy_title_exists'));
        $this->where('id', $comparison_id)->increment('added_count', 1);
        return $this->insertGetId([
                    'customer_id' => $customer_id,
                    'title' => $info['title'],
                    'origin_lang' => $info['origin_lang'],
                    'target_lang' => $info['target_lang'],
                    'share_flag' => $info['share_flag'],
                    'content' => $info['content'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 收藏或取消收藏 
     */
    public function favComparion($customer_id, $comparison_id) {
        $info = $this->where('id', $comparison_id)->first();
        check(!empty($info), Lang::get('comparion.comparion_not_exists'));
        check($info['customer_id'] != $customer_id, Lang::get('comparion.comparion_canot_copy'));
        $m_comparison_fav = new ComparisonFav();
        $m_comparison_fav->favComparion($customer_id, $comparison_id);
    }

    /**
     * 判断用户的对照表标题是否存在
     * @return boolean
     */
    private function check_title_exists($customer_id, $comparison_id, $title) {
        return $this->where('customer_id', $customer_id)->whereNot('id', $comparison_id)->where('title', trim($title))->exists();
    }

    private function get_content($contents) {
        $items = [];
        foreach ($contents as $item) {
            check(count($item) == 2, Lang::get('comparion.item_not_match'));
            if (array_key_exists('origin', $item) && array_key_exists('target', $item)) {
                check(!empty($item['origin']) && !empty($item['target']), Lang::get('comparion.item_not_match'));
                $items[] = implode(',', [$item['origin'], $item['target']]);
            } else {
                check(!empty($item[0]) && !empty($item[1]), Lang::get('comparion.item_not_match'));
                $items[] = implode(',', $item);
            }
        }
        return implode(';', $items);
    }

}
