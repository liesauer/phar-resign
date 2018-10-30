<?php

$file = isset($argv[1]) ? $argv[1] : '';

$raw_content = file_get_contents($file);

// 参考：http://php.net/manual/en/phar.fileformat.signature.php

$is_signed = substr($raw_content, -4, 4);

if ($is_signed !== 'GBMB') {
    exit('the phar has not been signed.');
}

// little-endian
$sign_flag = unpack('V', substr($raw_content, -8, 4))[1];

$sign_map = [
    0x0001 => [
        'name'   => 'MD5',
        'length' => 16,
    ],
    0x0002 => [
        'name'   => 'SHA1',
        'length' => 20,
    ],
    0x0004 => [
        'name'   => 'SHA256',
        'length' => 32,
    ],
    0x0008 => [
        'name'   => 'SHA512',
        'length' => 64,
    ],
];

$sign_start_pos = -(4 + 4 + $sign_map[$sign_flag]['length']);

$content = substr($raw_content, 0, $sign_start_pos);

$resign = hash($sign_map[$sign_flag]['name'], $content, true);

$resigned_content = substr_replace($raw_content, $resign, $sign_start_pos, -8);

file_put_contents("{$file}.resigned", $resigned_content);
