<?php

require_once './EU4EncodingConverter.php';

$str = '';
$whitespace = false;

switch (true) {
    # 命令行帶入參數
    case (isset($argc) && $argc > 1):
        # 只給一個參數
        if ($argc === 2) {
            # 參數是要轉換的字串而非單純指定 hex 帶空格
            if ($argv[1] !== EU4EncodingConverter::CMD_SPACE_PARAM) {
                $str = $argv[1];
            }
            # 沒給要轉換的字串而僅指定 hex 帶空格，直接結束
            else {
                exit(0);
            }
        }
        # 給兩個參數以上（第三個參數以後自動忽略）
        else if ($argc >= 3) {
            switch (true) {
                # 第一個參數是要轉換的字串，第二個參數指定 hex 帶空格
                case $argv[2] === EU4EncodingConverter::CMD_SPACE_PARAM && $argv[1] !== EU4EncodingConverter::CMD_SPACE_PARAM:
                    $str = $argv[1];
                    $whitespace = true;
                    break;
                # 第一個參數指定 hex 帶空格，第二個參數才是要轉換的字串
                case $argv[1] === EU4EncodingConverter::CMD_SPACE_PARAM && $argv[2] !== EU4EncodingConverter::CMD_SPACE_PARAM:
                    $str = $argv[2];
                    $whitespace = true;
                    break;
                # 前兩個參數都未指定 hex 帶空格，只抓第一個參數來轉換
                case $argv[1] !== EU4EncodingConverter::CMD_SPACE_PARAM && $argv[2] !== EU4EncodingConverter::CMD_SPACE_PARAM:
                    $str = $argv[1];
                    break;
                # 其他情況直接結束
                    exit(0);
            }
        }
        break;
    # 瀏覽器 URL 帶 GET 參數
    case (isset($_GET['s'])):
        header('Content-Type: text/plain; charset=UTF-8');
        $str = $_GET['s'];
        if (isset($_GET['space']) && !in_array(strtolower($_GET['space']), ['false', '0', 'null', 'no', 'none'])) {
            $whitespace = true;
        }
        break;
    # 未帶參數直接結束
    default:
        exit(0);
}

echo EU4EncodingConverter::getInstance()->encode($str, $whitespace)->get();
