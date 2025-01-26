<?php
namespace Sora_Kanata\Tor_Filter\Lib\File;
use Sora_Kanata\Tor_Filter\Lib\Error\Error_Manager;

class File_Manager
{
    private object $Error_Manager;

	function __construct()
	{
		$this->Error_Manager = new Error_Manager();
	}

	public function ReadToFile(string $File, string $Load_Mode = "array")
	{
		switch(strtolower($Load_Mode))
		{
			case "array":
				$data = array();
			break;
			default:
				$data = "";
			break;
		}
		$compression_protocol = "";
		$compression_protocol = $this->CompressionProtocol($File);
		$file_resource = fopen($compression_protocol . $File, "r");
		if($file_resource === false)
		{
			$this->Error_Manager->SetLog("ファイルが開けませんでした。");
			$this->Error_Manager->PrintLastError();
		}
		flock($file_resource, LOCK_SH);
			while(feof($file_resource) !== true)
			{
				$file_line = fgets($file_resource);
				if(is_array($data))
				{
					if(rtrim($file_line, "\r\n"))
					{
						$data[] = rtrim($file_line, "\r\n");
					}
				}
				else
				{
					$data .= $file_line;
				}

			}
		flock($file_resource, LOCK_UN);	
		fclose($file_resource);

		return $data;
	}

//保存
	public function WriteToFile(string $File, string $Data)
	{
		$write_flag = false;
		$compression_protocol = "";
		$compression_protocol = $this->CompressionProtocol($File);
		$file_resource = fopen($compression_protocol . $File, "w");
		if($file_resource === false)
		{
			$this->Error_Manager->SetLog("ファイルが開けませんでした。");
			$this->Error_Manager->PrintLastError();
		}
		flock($file_resource, LOCK_EX);
			$write_flag = fwrite($file_resource, $Data);
		flock($file_resource, LOCK_UN);
		if($Data and $write_flag === false)
		{
			$this->Error_Manager->SetLog("書き込めませんでした。");
			$this->Error_Manager->PrintLastError();
		}
		fclose($file_resource);

		return true;
	}

	private function CompressionProtocol(string $File_Path)
	{
		$compress_protocol = "";
		$file_ext = strtolower(pathinfo($File_Path, PATHINFO_EXTENSION));
		switch($file_ext)
		{
			case "gz" :
				$compress_protocol = "compress.zlib://";
			break;
			case "bz2" :
				$compress_protocol = "compress.bzip2://";
			break;
		}
		return $compress_protocol;
	}

	//解凍
	public function DataDecompression($Type, $Data)
	{
		switch($Type)
		{
			case "gz":
				$Data = gzdecode($Data);
				break;
			case "bz2":
				$Data = bzdecompress($Data);
				break;
		}
		return $Data;
	}

	//圧縮
	public function DataCompression($Type, $Data)
	{	
		switch($Type)
		{
			case "gz":
				$Data  = gzencode($Data);
				break;
			case "bz2":
				$Data = bzcompress($Data);
				break;
		}
		return $Data;
	}	

//FileLock
	public function LockToFile($Lock_File, $Type)
	{
		$file_resource = null;
		if(is_resource($Lock_File))
		{
			$file_resoruce = $Lock_File;
		}
		elseif(is_file($Lock_File))
		{
			$file_resource = fopen($Lock_File, "a");
			if($file_resource === false)
			{
				$this->Error_Manager->SetLog("LockFileが開けません");
				$this->Error_Manager->PrintLastError();
			}
		}
		
		switch($Type)
		{
			case "SH":
				$lock_type = LOCK_SH | LOCK_NB;
			break;
			case "EX":
				$lock_type = LOCK_EX | LOCK_NB;
			break;
			case "UN":
				$lock_type = LOCK_UN;
			break;
		}

		$flock_flag = flock($file_resoruce, $lock_type);
		if($flock_flag === false)
		{
			$this->Error_Manager->SetLog("ファイルロックの処理が失敗しました。");
			$this->Error_Manager->PrintLastError();
		}
		if($Type == "UN" and $flock_flag)
		{
			if(fclose($file_resource))
			{
				$file_resoruce = null; 
			}
		}

		return $file_resource;
	}

}