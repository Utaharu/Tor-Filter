# Tor Filterについて<br/>
 - Tor Exit Addressからのアクセスをチェック・制限する為のPHPスクリプトです。<br/>
    - Tor Exit Addressリストを元に、ユーザIPを検証します。<br/>
    - Tor Exit Addressリストは、設定した配布元からデータを取得します。
<br/>

# 呼び出し方<br/>
 組み込みたいスクリプトに、次のコードを記述してください。<br/>

 - tor_filter_caller.phpを使った方法<br/>

    ```
        require "./tor_filter/tor_filter_caller.php";
    ```
 <br/>

 - tor_filter_caller.phpを使わない方法<br/>

     `$tor_address_flag` は、`Tor Exit Addressリスト`を元に、`ユーザIPが一致した`場合、`bool型でtrue`が入ります。<br/>
    - ユーザIPをTor_Filterが自動取得して判別を利用する<br/>

        ```
            require "./tor_filter/lib/autoload/project_loader.php";
            $tor_filter  = new Sora_Kanata\Tor_Filter\Tor_Filter();
            $tor_address_flag = $tor_filter->IsTorAddress();
        ```
    <br/>

    - ユーザIPを指定して判別を利用する<br/>
        `"127.0.0.1"` の部分を変更し、ユーザIPを指定してくださしてください。<br/>

        ```
            require "./tor_filter/lib/autoload/project_loader.php";
            $tor_filter  = new Sora_Kanata\Tor_Filter\Tor_Filter("127.0.0.1");
            $tor_address_flag = $tor_filter->IsTorAddress();
        ```



