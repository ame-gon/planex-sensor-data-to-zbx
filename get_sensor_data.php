<?php

if ($argc < 2) 
{
    print_usage();
    return;
}

// jsonファイルから設定情報の読み込み
$config_file = $argv[1];
//指定したファイルの要素をすべて取得する
$config_json = file_get_contents($config_file);
//json形式のデータを連想配列の形式にする
$config_data = json_decode($config_json, true);

// 設定情報のパラメータ設定チェック
if ( check_config_params($config_data) == -1 ) {
    echo "Missing parameters in configuration file.\n";
    return;
}

// 固有値設定
// API サーバーのURL
$api_url = $config_data['api_url'];
// API サーバーで発行されたトークン
$token = $config_data['token'];
// センサーのタイプ
$type = $config_data['type'];
// センサーの MAC アドレス
$mac = $config_data['mac'];
// センサーの最新取得データを書き込むファイル
$data_file = $config_data['data_file'];
// センサーデータを送信する Zabbix サーバー
$zabbix_ip = $config_data['zabbix_ip'];
// Zabbix に設定されるアイテムごとのキー
$key_temp = $config_data['key_temp'];
$key_humidity = $config_data['key_humidity'];
$key_barometric_pressure = $config_data['key_barometric_pressure'];

// センサーが UTC 標準時での時刻サポート。
// スクリプト内の処理で使用する時刻も一時的に UTC にあわせる。
date_default_timezone_set('Etc/GMT');

// 現在時刻と15分まえの時刻を取得
$now = time();
$to = date('Y/m/d H:i:s', $now);
$from = date('Y/m/d H:i:s', $now - (60 * 15));
// 以下は1時間分のデータを取得するための時間設定
//$from = date('Y/m/d H:i:s', $now - (60 * 60));

// 基本的な設定値例は以下のとおり。
// $query = ['type'=>'WS-USB01-THP','mac'=>$mac,'from'=>'2019-06-11 11:22:00','to'=>'2019-06-11 11:23:00','token'=>$token];

// 生成した時間データを使用して、指定した直近時間のセンサー計測値を取得する。
$query = ['type'=>$type,'mac'=>$mac,'from'=>$from,'to'=>$to,'token'=>$token];

$response_json = file_get_contents($api_url . http_build_query($query));

// 結果は json 形式で返されるので配列に変換
$array_result = json_decode($response_json,true);

// 直近の中の最新値を取得
$array_count = count($array_result);
if ($array_count > 0) {
    $array_one_result = $array_result[$array_count - 1];
    $date = new DateTime($array_one_result[0]);
    $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
    // echo "時間： " . $array_one_result[0] . "\n";
    echo "時間(UT): " . $date->format('U') . "\n";
    echo "温度： " . $array_one_result[1] . "\n";
    echo "湿度： " . $array_one_result[2] . "\n";
    echo "気圧： " . $array_one_result[3] . "\n";

    file_put_contents($data_file, "Planex-Sensor " . $key_temp . " " . $date->format('U') . " " . $array_one_result[1] . "\n");
    file_put_contents($data_file, "Planex-Sensor " . $key_humidity . " " . $date->format('U') . " " . $array_one_result[2] . "\n", FILE_APPEND);
    file_put_contents($data_file, "Planex-Sensor " . $key_barometric_pressure . " " . $date->format('U') . " " . $array_one_result[3] . "\n", FILE_APPEND);

    // Zabbix に送信
    $cmd = 'zabbix_sender -z ' . $zabbix_ip . ' -T -i ' . $data_file;
    exec($cmd, $opt);

} else {
    echo "no data...\n";
}

// === 関数 ===
/*
    スクリプトの使い方表示
*/
function print_usage()
{
    echo "This is a getiing sensor data script.\n";
    echo "  $ php get_sensor_data.php <path of config>\n";
}

/*
    設定ファイルのパラメータチェック

    パラメータに不足なし：return 0
    パラメータに不足あり：return -1
*/
function check_config_params($config)
{
    if( isset($config['api_url']) && isset($config['token']) && 
        isset($config['type']) && isset($config['mac']) &&
        isset($config['data_file']) && isset($config['zabbix_ip']) && 
        isset($config['key_temp']) && isset($config['key_humidity']) && 
        isset($config['key_barometric_pressure']) ) {

        return 0;
    } else {
        return -1;
    }
}

?>
