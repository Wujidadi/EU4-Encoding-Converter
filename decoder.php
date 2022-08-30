<?php

require_once './EU4EncodingConverter.php';

$str = '';

switch (true) {
    # 命令行帶入參數
    case (isset($argc) && $argc > 1):
        $str = $argv[1];
        break;
    # 瀏覽器 URL 帶 GET 參數
    case (isset($_GET['s'])):
        header('Content-Type: text/plain; charset=UTF-8');
        $str = $_GET['s'];
        break;
    # 未帶參數直接結束
    default:
        exit(0);
}

echo EU4EncodingConverter::getInstance()->decode($str)->get();
