<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model{

    protected $table = "customer";

    /**
     * 获取用户列表
     * @param  array  $params 
     * @param  int $page   
     * @param  int $limit  
     */
    public function getCustomers($params, $page=1, $limit=20){
        $query=DB::table($this->table);
        $query->selectRaw('id,customer_no,email,level,status,created_at');
        $query->where('deleted_flag','N');
        if(!empty($params['keyword'])){
            $query->where(function($q) use($params){
                $keyword='%'.$params['keyword'].'%';
                $q->where('customer_no','like',$keyword)
                    ->orWhere('email','like',$keyword);
            });
        }
        $total=$query->clone()->count();
        $query->skip(($page-1)*$limit)->limit($limit);
        $results=$query->orderBy('id','desc')->get()->toArray();
        return ['data'=>$results, 'total'=>$total];
    }

    /**
     * 编辑用户
     * @param  int $customer_id 
     * @param  array $data        
     */
    public function editCustomer($customer_id, $data){
        $_data=[];
        if(!empty($data['password'])){
            $_data['password']=password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $_data['email']=$data['email'];
        $_data['level']=$data['level'];
        return $this->where('id',$customer_id)->update($_data);
    }

    /**
     * 操作用户状态
     * @param  int $customer_id 
     * @param  int $status
     */
    public function changeCustomerStatus($customer_id, $status){
        $this->where('id',$customer_id)->update(['status'=>$status]);
    }

    public function changePassword($customer_id, $password){
        $this->where('id',$customer_id)->update([
            'password'=>password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * 注册用户
     * @param  string $email    
     * @param  string $password 
     * @return 
     */
    public function registerCustomer($params){
        return $this->insertGetId([
            'customer_no'=>'C'.date('YmdHis').random_int(10000, 99999),
            'email'=>$params['email'], 
            'password'=>password_hash($params['password'], PASSWORD_DEFAULT),
            'storage'=>80*1024*1024,
            'level'=>'common',
            'status'=>'enabled',
            'created_at'=>date('Y-m-d H:i:s'),
            'deleted_flag'=>'N',
        ]);
    }

    public function getCustomerByEmail($email){
        if(empty($email)){
            return [];
        }
        $customer=$this->where('email',$email)->where('deleted_flag','N')->first();
        return empty($customer) ? [] : $customer->toArray();
    }

    public function getCustomerInfo($customer_id){
        $customer=$this->selectRaw('id,customer_no,email,status,level,status,password,storage')->where('id',$customer_id)->first();
        return empty($customer) ? [] : $customer->toArray();
    }

    public function getCustomerAvaiableStorage($customer_id){
        $customer=$this->getCustomerInfo($customer_id);
        $m_translate=new Translate();
        $used=$m_translate->getCustomerAllFileSize($this->customer_id);
        return $customer['storage']-$used;
    }
}
