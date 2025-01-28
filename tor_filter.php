<?php
namespace Sora_Kanata\Tor_Filter;

use Sora_Kanata\Tor_Filter\Function_Config;
use Sora_Kanata\Tor_Filter\Lib\Converter\Source_Data_Converter;
use Sora_Kanata\Tor_Filter\Lib\Converter\Source_Data_Downloader;
use Sora_Kanata\Tor_Filter\Lib\User\User_Manager;
use Sora_Kanata\Tor_Filter\Lib\File\File_Manager;
use Sora_Kanata\Tor_Filter\Lib\Error\Error_Manager;

class Tor_Filter
{

	private object $Config;
	private object $User;

	private bool $Function_Enabled = false;
	private bool $Update_Flag = false;

	private object $File_Manager;
	private object $Error_Manager;

	private array $TorAddress_List = array();

	function __construct($User_IpAddress = "")
	{
		$this->Update_Flag = false;
		$this->Config = new Function_Config();
		if($this->Config->Function_Enable)
		{
			$this->Function_Enabled = true;
		}
		$this->User = new User_Manager($User_IpAddress);
		$this->File_Manager = new File_Manager;
		$this->Error_Manager = new Error_Manager;

		if($this->Function_Enabled)
		{
			/* # - Data File Update - # */
			if($this->ShouldUpdateTorAddressList() === true)
			{
				$this->DownloadTorAddressList();
				$this->RefreshUpdateStatus();
				$this->Update_Flag = true;
			}
			/* # - Load Tor Address Data - # */
			$this->LoadTorAddressList();
		}
	}

	private function DownloadTorAddressList()
	{
		foreach($this->Config->Distributor_Setting as $distributor)
		{
			if(isset($distributor['download']))
			{
				$download_flag = false;
				$downloader = new Source_Data_Downloader($distributor['download']['url']);
				$download_flag = $downloader->Download();
				if($download_flag)
				{
					$this->File_Manager->WriteToFile($distributor['download']['save_path'], $downloader->GetDownloadData());
					if(isset($distributor['convert']))
					{
						$this->ApplyTorAddressListUpdate($distributor['convert'], $downloader->GetDownloadData());
					}
				}
			}
		}
		return;
	}

	private function ApplyTorAddressListUpdate(array $Convert_Options, string $Download_Data)
	{
		$converter = new Source_Data_Converter($Convert_Options['match'], $Convert_Options['data_order_key']);
		$converter->ExtractStringToArray($Download_Data);
		$convert_data = $converter->GetDataList();
		if(isset($Convert_Options['data_sort']))
		{	
			$this->SortData($convert_data, $Convert_Options['data_sort']);
		}
		$save_data = "";
		if(is_array($convert_data))
		{
			foreach($convert_data as $data_items)
			{
				if($data_items and is_array($data_items))
				{
					if(isset($Convert_Options['array_to_string']))
					{
						$pattern_list = array_keys($data_items);
						$pattern_list = preg_replace("/(.+)/", "/@\\1/", $pattern_list);
						$add_data = preg_replace($pattern_list, $data_items, $Convert_Options['array_to_string']);
						if($add_data)
						{
							$save_data .= $add_data;
						}
					}	
				}
			}
		}
		$this->File_Manager->WriteToFile($Convert_Options['save_path'], rtrim($save_data, "\r\n"));

		return;
	}

	private function LoadTorAddressList()
	{
		$load_flag = false;
		$this->TorAddress_List = array();
		if(isset($this->Config->Distributor_Setting) and is_array($this->Config->Distributor_Setting))
		{
			foreach($this->Config->Distributor_Setting as $distributor)
			{
				if(isset($distributor['convert']['save_path']) and file_exists($distributor['convert']['save_path']))
				{
					$read_data = $this->File_Manager->ReadToFile($distributor['convert']['save_path']);
					if(is_array($read_data) and count($read_data) > 0)
					{
						$this->TorAddress_List = array_merge_recursive($this->TorAddress_List, $read_data);
						$load_flag = true;
					}
				}
			}
		}
		if($load_flag === false)
		{
			$this->Error_Manager->SetError("アドレスリストが読み込まれませんでした。");
			$this->Error_Manager->PrintLastError();
		}
		return $load_flag;
	}

	public function IsTorIpAddress()
	{
		$tor_address_flag = false;
		if(is_array($this->TorAddress_List) and $this->User->IpAddress())
		{
			$check_result = false;
			$check_result = array_search($this->User->IpAddress(), $this->TorAddress_List);
			if($check_result !== false)
			{
				$tor_address_flag = true;
			}
		}
		return $tor_address_flag;
	}

	private function RefreshUpdateStatus()
	{
		$this->File_Manager->WriteToFile($this->Config->Update_Setting['status_file'], time());
		return;
	}

	private function ShouldUpdateTorAddressList()
	{
		// - # Load Latest Update TimeStamp
		$latest_update_time = 0;
		if(file_exists($this->Config->Update_Setting['status_file']))
		{
			$latest_update_time = $this->File_Manager->ReadToFile($this->Config->Update_Setting['status_file']);
			if(isset($latest_update_time[0]))
			{
				$latest_update_time = intval(trim($latest_update_time[0], "\r\n"));
			}
			if(!is_numeric($latest_update_time))
			{
				$latest_update_time = 0;
			}
		}
		// - # Next Update TimeStamp
		$next_update_time = $latest_update_time + ($this->Config->Update_Setting['update_time'] * 3600);
		if($next_update_time <= time() and $latest_update_time <= $next_update_time)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

    private function SortData(array &$Target_Array, array $Sort_Order)
    {
        $return_flag = false;
        $sort_argument = array();
        if(is_array($Target_Array) and count($Target_Array) > 1)
        {
			if(is_array($Sort_Order) and count($Sort_Order) > 0)
			{
				foreach($Sort_Order as $name => $sort_values)
				{
					$sort_argument[] = &array_column($Target_Array, $name);						
					if(isset($sort_values['order']))
					{
						$sort_argument[] = $sort_values['order'];
					}
					if(isset($sort_values['flags']))
					{
						$sort_argument[] = $sort_values['flags'];
					}
				}
			}
        }
        $sort_argument[] = &$Target_Array;
        call_user_func_array("array_multisort", $sort_argument);
        $return_flag = true;
        return $return_flag;
    }

	public function IsFunctionEnabled()
	{
		return $this->Function_Enabled;
	}

	public function Error(string $Error_Title, string $Error_Description)
	{
		$this->Error_Manager->SetLog($Error_Description, $Error_Title);
		$this->Error_Manager->PrintLastError();
		exit;
	}

	public function GetVersion()
    {
        return __NAMESPACE__ . " Ver.2.2";
    }

}