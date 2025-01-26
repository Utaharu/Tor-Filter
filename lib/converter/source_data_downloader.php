<?php
namespace Sora_Kanata\Tor_Filter\Lib\Converter;

use Sora_Kanata\Tor_Filter\Lib\File\File_Manager;
use Sora_Kanata\Tor_Filter\Lib\Error\Error_Manager;

class Source_Data_Downloader
{
	private $Data;
	private string $Download_Path = "";

	private object $File_Manager;
	private object $Error_Manager;

	function __construct(string $Download_Url)
	{
		$this->Download_Path = $Download_Url;
		$this->File_Manager = new File_Manager();
		$this->Error_Manager = new Error_Manager();
	}

	public function Download()
	{
		$download_flag = false;
		$this->Data = "";
		umask(0);
		if($this->Download_Path)
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->Download_Path);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 60);
			$this->Data = curl_exec($curl);
			curl_close($curl);

			// - # Decompression
			$load_file_ext = pathinfo($this->Download_Path, PATHINFO_EXTENSION);
			$this->Data = $this->File_Manager->DataDecompression($load_file_ext, $this->Data);

			if($this->Data)
			{
				$download_flag = true;
			}
		}
		return $download_flag;
	}

	public function GetDownloadData()
	{
		return $this->Data;
	}

}