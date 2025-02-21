<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class User extends Model {

    protected $table = "user";

    public function getUsers(Request $request, $page, $limit) {
        $query = $this->selectRaw('name,email,created_at,updated_at');
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $query->skip(($page - 1) * $limit)->limit($limit);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getUser($user_id) {
        if (empty($user_id)) {
            return [];
        }
        $user = $this->where('id', $user_id)->where('deleted_flag', 'N')->first();
        return empty($user) ? [] : $user->toArray();
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('created_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('created_at', $createAts);
        }
    }

    /**
     * 获取用户的邮箱
     * @param  array $user_id_arr 
     * @return 
     */
    public function getEmailByUsers($user_id_arr) {
        if (empty($user_id_arr)) {
            return [];
        }
        return $this->whereIn('id', $user_id_arr)->pluck('email')->toArray();
    }

    /**
     * 获取邮箱
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function getAuthByEmail($email) {
        if (empty($email)) {
            return [];
        }
        $user = $this->where('email', $email)->where('deleted_flag', 'N')->first();
        return empty($user) ? [] : $user->toArray();
    }

    /**
     * 获取合同列表
     * @param int $userId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($userId, Request $request) {
        $user = $this->where('email', $request->email)
                ->where('deleted_flag', 'N')
                ->whereNot('id', $userId)
                ->first();
        if (!empty($user)) {
            check(false, '该邮箱用户已存在');
        }

        $username = $this->where('name', $request->name)
                        ->whereNot('id', $userId)
                        ->where('deleted_flag', 'N')->first();
        if (!empty($username)) {
            check(false, '该用户已存在');
        }
        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'name' => !empty($request->name) ? trim($request->name) : trim($request->name),
            'email' => !empty($request->email) ? trim($request->email) : null,
            'deleted_flag' => 'N'
        ];
        if (!empty($request->password)) {
            $length = mb_strlen($request->password);
            check($length >= 6 && $length <= 16, '密码必须大于等于6位小于等于16位字符串');
            $data['password'] = password_hash($request->password, PASSWORD_DEFAULT);
        }
        $flag = $this->where('id', $userId)->update($data);
        return $flag;
    }

    /**
     * 获取合同列表
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function added(Request $request) {
        $user = $this->where('email', $request->email)->where('deleted_flag', 'N')->first();
        if (!empty($user)) {
            check(false, '该邮箱用户已存在');
        }
        $username = $this->where('name', $request->name)->where('deleted_flag', 'N')->first();
        if (!empty($username)) {
            check(false, '该用户已存在');
        }
        $userId = $this->insertGetId([
            'created_at' => date('Y-m-d H:i:s'),
            'name' => !empty($request->name) ? trim($request->name) : trim($request->name),
            'email' => !empty($request->email) ? trim($request->email) : null,
            'password' => password_hash($request->password, PASSWORD_DEFAULT),
            'deleted_flag' => 'N'
        ]);

        return $userId;
    }

    /**
     * 获取合同列表
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return $this->whereIn('id', $ids)->update(['deleted_flag' => 'Y']);
    }

    protected function getOrder(&$query) {
        $query->orderBy('created_at', 'DESC');
    }

    public function getTimeByType($type) {
        $createAts = [];
        switch (strtolower($type)) {
            case 'today':
                $createAts[0] = date('Y-m-d');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'this_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('this week Monday', $time));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'past_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('-6 days', $time));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'last_week':
                $time = time();
                $createAts[0] = date('Y-m-d', strtotime('last week Monday', $time));
                $createAts[1] = date('Y-m-d 23:59:59', strtotime('this week Monday', $time) - 1);
                break;
            case 'this_month':
                $createAts[0] = date('Y-m-01');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'this_quarter':
                $month = date('m');
                $season = (int) ($month / 3);
                if ($month % 3 > 0) {
                    $season += 1;
                }
                if ($season === 1) {
                    $createAts[0] = date('Y-01-01');
                } else if ($season === 2) {
                    $createAts[0] = date('Y-04-01');
                } else if ($season === 3) {
                    $createAts[0] = date('Y-07-01');
                } else {
                    $createAts[0] = date('Y-10-01');
                }
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'last_month':
                $createAts[0] = date('Y-m-01', strtotime('-1 months'));
                $createAts[1] = date('Y-m-d H:i:s', strtotime(date('Y-m-01')) - 1);
                break;
            case 'last_3_months':
                $createAts[0] = date('Y-m-d', strtotime('-3 months'));
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            case 'this_year':
                $createAts[0] = date('Y-01-01');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
            default :
                $createAts[0] = date('Y-m-d');
                $createAts[1] = date('Y-m-d 23:59:59');
                break;
        }
        return $createAts;
    }

}
