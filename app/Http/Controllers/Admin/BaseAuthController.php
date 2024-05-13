<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\AdminUser;

abstract class BaseAuthController extends BaseController {

    /**
     * 登录后台用户id
     * @var integer
     */ 
    protected $admin_user_id=0;

    /**
     * 登录用户信息
     * @var array
     */
    protected $admin_user;

    /**
     * 过滤的
     * @var array
     */
    protected $skip_auth=[];

    public function __construct(){

        parent::__construct();
        return;
        $auth = Auth()->guard('admin');
        try {
            check($auth->check(false), '认证失败，请重新登录！',401);
            $user = $auth->user();
            check($user->status, '账户已被禁用',401);
        } catch (TokenExpiredException $e) {
            check(false, $e->getMessage(),401);
        } catch (TokenInvalidException $e) {
            check(false, $e->getMessage(),401);
        } catch (JWTException $e) {
            check(false, $e->getMessage(),401);
        }
        $this->user_id=$user->user_id;
        // $this->check_permission($user);
    }

    /**
     * 权限验证
     * @return 
     */
    public function check_permission($user) {
        $user_id=$user->user_id;
        $is_super = $user->is_super;
        // 超管直接返回
        if ($is_super == 1)
            return;
        $routes=Route::getRoutes();
        $current_route=Request::route()->uri();
        $api_method = Request::getMethod();
        $uri = '';
        foreach ($routes as $route) {
            if ($route->action['uses'] == $current_route) {
                $uri = $route->uri;
            }
        }
        // check(!empty($uri), '没有权限访问', 401);

        $uri = preg_replace('/\:[^}]+/', '', $uri);

        if (in_array(strtolower($uri), $this->skip_auth))
            return;

        $menus = DB::table('admin_menus', 'm')
          ->select('m.api_url', 'm.include', 'm.api_alias', 'm.api_method', 'rm.data_level')
          ->join('admin_role_with_menus as rm', 'rm.menu_id', '=', 'm.menu_id')
          ->join('admin_with_roles as ar', 'ar.role_id', '=', 'rm.role_id')
          ->join('admin_roles as r', 'r.role_id', '=', 'ar.role_id')
          ->where('ar.user_id', $user_id)
          ->where('r.is_check', 1)
          ->where('r.deleted_flag', 'N')
          ->get()
          ->toArray();

        $alias = [];

        // echo $uri;exit;
        // print_r($menus);exit;
        foreach ($menus as $menu) {
            if ($uri == $menu->api_url && $api_method == strtoupper($menu->api_method)) {
                return;
            }
            $include_arr = explode(',', $menu->include);
            $include_arr = array_filter(array_map(function($value) {
                  return trim($value);
              }, $include_arr));
            array_push($alias, $menu->api_alias);
            !empty($include_arr) && array_push($alias, ...$include_arr);
        }

        $alias = array_filter(array_unique($alias));
        // print_r($alias);exit;
        $menus = DB::table('admin_menus')->select('api_url', 'api_method')->whereIn('api_alias', $alias)->get()->toArray();
        foreach ($menus as $menu) {
            if ($uri == $menu->api_url && $api_method == strtoupper($menu->api_method)) {
                $request->merge(['_data_level' => 1]);
                return;
            }
        }

        check(false, '没有权限访问', 401);
    }
}
