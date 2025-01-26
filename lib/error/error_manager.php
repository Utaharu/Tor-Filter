<?php
namespace Sora_Kanata\Tor_Filter\Lib\Error;

use Sora_Kanata\Tor_Filter\Lib\Error\Error_Printer;
use Sora_Kanata\Tor_Filter\Lib\Error\Data_Object\Error_Data_Object;

class Error_Manager
{
    /* ##### Property ##### */
	private string $Project_Name = "";
	private array $Error_Data_List = array();
	private $Error_Printer = null;

	function __construct()
	{
		list(,$this->Project_Name) = explode("\\", __NAMESPACE__);
		$this->Error_Printer = new Error_Printer();
	}

	public function SetLog(string $Description, string $Title = "", string $File = null, string $Class = null, string $Function = null, ?int $Line_No = null)
	{
		if($Title == "")
		{
			$Title = $this->Project_Name . " Error";
		}
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
		if(isset($backtrace[0]))
		{
			if(is_null($File) or !is_file($File))
			{
				$File = $backtrace[0]['file'];
			}
			if(is_null($Class))
			{
				$Class = $backtrace[0]['class'];
			}
			if(is_null($Function))
			{
				$Function = $backtrace[0]['function'];
			}
			if(is_null($Line_No))
			{
				$Line_No = $backtrace[0]['line'];
			}
		}
		$error_data_object = new Error_Data_Object($Title, $Description, $File, $Class, $Function, $Line_No);
		if($error_data_object instanceof Error_Data_Object and $error_data_object->CheckRequiredFields())
		{
			$this->Error_Data_List[] = $error_data_object;
		}

		return;
	}

	public function PrintLastError()
	{
		$error_data_object = array_pop($this->Error_Data_List);
		if($error_data_object instanceof Error_Data_Object)
		{
			$this->Error_Printer->Print($error_data_object->Title(), $error_data_object->Description());
			exit;
		}
	}

	public function GetLog()
	{
		return $this->Error_Data_List;
	}
}