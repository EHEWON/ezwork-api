<?php


if (!function_exists('generateRandomString')) {
    function generateRandomString($length) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}

if (!function_exists('generateRandomInteger')) {
    function generateRandomInteger($length) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}

if (!function_exists('create_uuid')) {
    function create_uuid($prefix=""){
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return $prefix.$uuid ;
    }
}

if (!function_exists('spend_time')) {
    function spend_time($start_time, $end_time) {
        if(empty($end_time)){
            return "";
        }
        $diff = strtotime($end_time) - strtotime($start_time);
        $str = '';
        if ($diff <= 60) {
            $str = "00'".$diff."''";
        } elseif ($diff < 3600) {
            $minutes=intval(floor($diff/60));
            $seconds=$diff%60;
            $str=str_pad($minutes, 2, '0', STR_PAD_LEFT)."'".$seconds."''";
        }
        return $str;
    }
}

if (!function_exists('check')) {
    function check(bool $assert, $message, $code = 1) {
        if (!$assert) {
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode([
                'data' => [],
                'code' => $code,
                'message' => $message], JSON_UNESCAPED_UNICODE);
            die;
        }
    }
}

if (!function_exists('ok')) {
    function ok($data=[]) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode([
            'data' => $data,
            'code' => 0,
            'message' => ''], JSON_UNESCAPED_UNICODE);
        die;
    }
}

if (!function_exists('email_hidden')){
    function email_hidden($email){
        $parts=@explode('@', $email);
        if(count($parts)==1) return '****';
        $name=substr_replace($parts[0], '*', 2);
        return $name.'@'.$parts[1];
    }
}