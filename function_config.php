<?php
namespace Sora_Kanata\Tor_Filter;

class Function_Config
{
    public bool $Function_Enable = false;
    public array $Update_Setting = array();
	public array $Distributor_Setting = array();

    function __construct()
    {
        /* Tor_Filterの機能 (OFF = false , ON = true) */
        $this->Function_Enable = true;

		/*
			Tor_Filter - データの更新設定
		*/
		$this->Update_Setting =
			array(
				/* 更新ステータスファイルの場所 */
				"status_file" => dirname(__FILE__) . "/data/status.dat",
				/* 何時間毎にデータを更新するか (1-24) */
				"update_time" => 24,
			);

		/*
			Tor Exit Address - データの配布元の設定
			 * 複数指定可能
		*/
		$this->Distributor_Setting =
			array(
				"tor_project" =>
					array(
						"download" =>
							array(
								/* Download URL */
								"url" => "https://check.torproject.org/exit-addresses",
								/*
									DownLoad Data - ファイルの保存先
										* 圧縮対応拡張子 = gz,bz2
								*/
								"save_path" => dirname(__FILE__) . "/data/temp/exit_addresses.gz"
							),
						"convert" =>
							array(
								/* 抽出するデータの正規表現 */
								"match" => "/^.*?ExitAddress.*?(?P<ip>(?:[0-9]{1,3}\.?){4})[^\r\n]+?$/m", 
								/* 抽出したデータの配列順序の指定 */
								"data_order_key" => array("ip"),
								/* 抽出したデータ配列のソートの設定 */
								"data_sort" =>
									array(
										"ip" =>
											array(
												"order" => SORT_ASC,
												"flags" => SORT_NUMERIC
											)
										),
								/* 抽出したデータ配列を文字列に置き換える為の設定 */
								"array_to_string" => "@ip\r\n",
								/*
									Converted Data - ファイルの保存先
										* 圧縮対応拡張子 = gz,bz2
								*/
								"save_path" => dirname(__FILE__) . "/data/tor_exit_addresses_list.gz"
							)
					)
			);
    }
}