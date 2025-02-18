<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\ComparisonFav;

/**
 * 翻译对照表
 */
class Prompt extends Model {

    protected $table = "prompt";

    /**
     * 获取我的提示语
     * @param  array  $params
     * @param  int $page
     * @param  int $limit
     */
    public function getMyPrompts($params, $page = 1, $limit = 20) {
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
        $query->selectRaw('c.id,c.title,c.added_count,c.created_at,c.content,c.share_flag');
        $query->skip(($page - 1) * $limit)->limit($limit);
        $results = $query->orderBy('id', 'desc')->get()->toArray();
        return ['data' => $results, 'total' => $total];
    }

    /**
     * 获取广场提示语
     * @param  array  $params
     * @param  int $page
     * @param  int $limit
     */
    public function getSharedPrompts($params, $page = 1, $limit = 20) {
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
        $query->selectRaw('c.id,c.customer_id,c.title,c.content,c.share_flag,c.added_count,c.created_at,c.updated_at,IF(ISNULL(f.prompt_id),0,1) faved');
        $query->leftJoin('prompt_fav as f', function($join) use ($params) {
            $join->on('c.id', '=', 'f.prompt_id')
                    ->where('f.customer_id', '=', $params['customer_id']);
        });
        if (!empty($params['porder'])) {
            switch ($params['porder']) {
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
            $result->email = email_hidden(DB::table('customer')
                            ->where('id', $result->customer_id)
                            ->value('email'));
        }
        return ['data' => $results, 'total' => $total];
    }

    /**
     * 获取提示语数据
     */
    public function getPrompt($prompt_id) {
        return $this->where('id', $prompt_id)->where('deleted_flag', 'N')->first();
    }

    /**
     * 获取用户的提示语数据
     */
    public function getCustomerPrompt($prompt_id, $customer_id) {
        return $this->where('id', $prompt_id)->where('customer_id', $customer_id)->where('deleted_flag', 'N')->first();
    }

    /**
     * 获取用户的所有提示语数据
     */
    public function getCustomerPormpts($customer_id) {
        return $this->where('customer_id', $customer_id)->where('deleted_flag', 'N')->get()->toArray();
    }

    /**
     * 添加翻译提示语数据
     */
    public function addPrompt($params) {
        return $this->insertGetId([
                    'customer_id' => $params['customer_id'],
                    'title' => $params['title'],
                    'share_flag' => $params['share_flag'] ?? 'N',
                    'content' => $params['content'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 编辑提示语数据
     */
    public function editPrompt($Prompt_id, $params) {
        $customer_id = $params['customer_id'];
        $exists = $this->check_title_exists($customer_id, $Prompt_id, $params['title']);
        check(!$exists, Lang::get('prompt.title_exists'));
        $info = $this->where('customer_id', $customer_id)->where('id', $Prompt_id)->first();
        check(!empty($info), Lang::get('prompt.prompt_not_exists'));
        return $this->where('id', $Prompt_id)->update([
                    'title' => $params['title'],
                    'customer_id' => $params['customer_id'],
                    'share_flag' => $params['share_flag'] ?? 'N',
                    'content' => $params['content'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 编辑分享状态
     */
    public function editPromptShare($customer_id, $prompt_id, $share_flag) {
        $info = $this->where('customer_id', $customer_id)->where('id', $prompt_id)->first();
        check(!empty($info), Lang::get('prompt.prompt_not_exists'));
        $this->where('id', $prompt_id)->update(['share_flag' => $share_flag]);
    }

    /**
     * 删除提示语
     */
    public function delPrompt($customer_id, $prompt_id) {
        $info = $this->where('customer_id', $customer_id)->where('id', $prompt_id)->first();
        check(!empty($info), Lang::get('prompt.prompt_not_exists'));
        $this->where('id', $prompt_id)->update(['deleted_flag' => 'Y']);
    }

    /**
     * 复制提示语
     */
    public function copyPrompt($customer_id, $prompt_id) {
        $info = $this->where('id', $prompt_id)->first();
        check(!empty($info), Lang::get('prompt.prompt_not_exists'));
        check($info['customer_id'] != $customer_id, Lang::get('prompt.prompt_canot_copy'));
        $exists = $this->check_title_exists($customer_id, 0, $info['title']);
        check(!$exists, Lang::get('prompt.prompt_copy_title_exists'));
        $this->where('id', $prompt_id)->increment('added_count', 1);
        return $this->insertGetId([
                    'customer_id' => $customer_id,
                    'title' => $info['title'],
                    'share_flag' => $info['share_flag'],
                    'content' => $info['content'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N',
        ]);
    }

    /**
     * 收藏或取消收藏
     */
    public function favPrompt($customer_id, $prompt_id) {
        $info = $this->where('id', $prompt_id)->first();
        check(!empty($info), Lang::get('prompt.prompt_not_exists'));
        check($info['customer_id'] != $customer_id, Lang::get('prompt.prompt_canot_fav'));
        $m_comparison_fav = new PromptFav();
        $m_comparison_fav->favPrompt($customer_id, $prompt_id);
    }

    /**
     * 判断用户的提示语标题是否存在
     * @return boolean
     */
    private function check_title_exists($customer_id, $prompt_id, $title) {
        return $this->where('customer_id', $customer_id)->whereNot('id', $prompt_id)->where('title', trim($title))->exists();
    }

}
