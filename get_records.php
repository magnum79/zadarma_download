<?php

use Zadarma_API\Api;

require_once __DIR__.DIRECTORY_SEPARATOR.'include.php';

define('USE_SANDBOX', false);

$zd = new \Zadarma_API\Client(KEY, SECRET, USE_SANDBOX);

$params['take'] = 200;
$params['skip'] = 45202;
$totalCount = 999999;
while ($params['skip'] <= $totalCount) {
    $count = 0;
    $result = json_decode($zd->call('/v1/zcrm/calls', $params));
    $totalCount = $result->data->totalCount;
    foreach ($result->data->calls as $call) {
        $count++;
        $record = json_decode($zd->call('/v1/pbx/record/request/', ['pbx_call_id'=>$call->pbx_call_id]));
        if ($record->status != 'error') {
            $filesaved = false;
            foreach ($record->links as $link) {
                $filesaved = savefile($link);
            };
            if ($filesaved) {
                saveJSON(__DIR__ . '/call_data/' . $call->pbx_call_id, $record);
            }
        }
        echo $totalCount ."\t" . ($count + $params['skip']) . PHP_EOL;
    }
    $params['skip'] += $params['take'];
    $count += $params['take'];
}

function saveJSON($fileName, $data, $flags = JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) {
    $json = json_encode($data, $flags);
    $ext = ".json";
    $file = $fileName . $ext;
    file_put_contents($file, $json);
}

function savefile($url) {
    $file = __DIR__ . '/voice_files/' . basename($url);

    if (!file_exists($file) || filesize ($file) === 0) {
        file_put_contents($file, fopen("$url", 'r'));
        return true;
    }
    return false;
}

exit;