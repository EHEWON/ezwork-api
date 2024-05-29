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