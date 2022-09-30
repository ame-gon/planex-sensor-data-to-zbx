# 本リポジトリの説明

本リポジトリは、Planex の `どこでもセンサー` を使用したセンサーデータを Zabbix にデータ送信するスクリプトです。

## 動作概要
本スクリプトは以下の動作をします。

- Planex Cloud にアクセスし、API を使用して監視データを取得します。
    - 取得できる監視データ：温度、湿度、気圧
- 取得した監視データを zabbix_sender を使用して、Zabbix サーバーに送信します。

Planex から API で取得するデータは Planex Cloud で保存されている直近15分間のうち直近のデータを取得します。
結果、Zabbix に送信されるデータは直近のデータのみとなります。

## 動作要件
- php が使用できること

## 使い方
### 事前準備
#### Zabbix 監視設定
- Zabbix にセンサー情報を監視するホストを作成します。
- 作成したホストに以下のアイテムを作成します。タイプは Zabbix トラッパーとしてください。
    - temp(気圧)
    - humidity(湿度)
    - pressure(気圧)
- Zabbix の IP アドレスと、上記3アイテムのキーをメモしておきます。(次項で使用します。)

#### スクリプト設定
- 本リポジトリを適当な場所に clone します。
- config.json.org をコピーして同じ場所に config.json を作成します。
- config.json を修正します。各パラメータに適当な設定を行ってください。
    - "api_url"
        - Planex Cloud のAPI情報でご確認ください。
        - 2022年9月現在では以下設定で動作確認済です。
        - "https://svcipp.planex.co.jp/api/get_data.php?"
    - "token"
        - Planex Cloud のデバイス情報で TOKEN に表示されている情報を設定してください。
    - "type"
        - Planex Cloud のデバイス情報で デバイス に表示されている情報を設定してください。
    - "mac"
        - Planex Cloud のデバイス情報で mac に表示されている情報を設定してください。
        - 設定ファイルに記載する際には、コロン : を削除して英数字をつなげた記載にしてください。
    - "data_file"
        - Planex Cloud が保持している直近のセンサーデータを保存するファイルへのフルパス指定してください。
    - "zabbix_ip"
        - センサーデータを監視する Zabbix の IP を設定してください。
    - "key_temp" 
        - Zabbix に作成した temp の監視アイテムのキーを設定してください。
    - "key_humidity"
        - Zabbix に作成した humidity の監視アイテムのキーを設定してください。
    - "key_barometric_pressure"
        - Zabbix に作成した pressure の監視アイテムのキーを設定してください。

### スクリプトの実行
以下のコマンドでスクリプトを実行してください。

```
php get_sensor_data.php <設定ファイルへのパス>
```

正常に実行されると、以下のようなログが表示され Zabbix にデータが送信されます。

```
時間(UT): 1664502092
温度： 30.26
湿度： 47.4
気圧： 1016.3
```

## 動作確認情報
### php バージョン
- PHP 7.2.34

### センサー
- WS-USB01-THP
    - その他は製品を持っていないのでわかりません。。。

## 免責事項
- 本スクリプトはあくまでも自己責任でご利用ください。
- Planex の API 仕様が変更になる可能性もあることを考慮して API サーバーへの URL 等変更できるようにしていますが、それでも対応できない仕様変更があった場合にはご容赦ください。
- Planex Cloud へのアクセスは Planex Cloud に注意があるように 1デバイスあたり500回/日 を超えないようにご注意ください。