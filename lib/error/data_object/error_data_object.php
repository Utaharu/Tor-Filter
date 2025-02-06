<?php
namespace Sora_Kanata\Tor_Filter\Lib\Error\Data_Object;

class Error_Data_Object
{
    /* # Proparty */
    /* # - Object Config */
    private array $Object_Config = array();
    /* # - Error Data - # */
    private string $Title = "";
    private string $Description = "";
    private string $File_Path = "";
    private int $File_Line_Number = -1;
    private string $Class_Name = "";
    private string $Function_Name = "";
    private int $TimeStamp = -1;

    private array $Required_Flags = array();
    private array $Written_Flags = array();

    function __construct(string $Title = null, string $Description = null , string $File = null, string $Class = null , string $Function = null, ?int $Line_Number = null)
    {
        $this->Object_Config =
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "title", "value" => &$this->Title, "method_name" => "Title", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "description", "value" => &$this->Description, "method_name" => "Description", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "file_path", "value" => &$this->File_Path,  "method_name" => "FilePath", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 3, "data_name" => "line_number", "value" => &$this->File_Line_Number, "method_name" => "LineNumber", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 4, "data_name" => "class_name", "value" => &$this->Class_Name,  "method_name" => "ClassName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 5, "data_name" => "function_name", "value" => &$this->Function_Name,  "method_name" => "FunctionName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 6, "data_name" => "timestamp", "value" => &$this->TimeStamp,  "method_name" => "", "readonly_flag" => true, "required_flag" => false)
                    )
            );
        $this->Title($Title);
        $this->Description($Description);
        $this->FilePath($File);
        $this->ClassName($Class);
        $this->FunctionName($Function);
        $this->LineNumber($Line_Number);
        $this->TimeStamp = time();
    }

    private function IsReadonly(string $Method_Name)
    {
        $readonly_flag = false;
        if(isset($this->Object_Config['DataItems']))
        {
            $method_name_list = array_column($this->Object_Config['DataItems'], "method_name");
            $search_key = array_search($Method_Name, $method_name_list);
            if($search_key !== false)
            {
                if(isset($this->Object_Config['DataItems'][$search_key]['readonly_flag']) and is_bool($this->Object_Config['DataItems'][$search_key]['readonly_flag']))
                {
                    $readonly_flag = boolval($this->Object_Config['DataItems'][$search_key]['readonly_flag']);
                }
            }
        }
        return $readonly_flag;
    }

    public function Title($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->Title;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            $this->Title = $Value;
        }
    }

    public function Description($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);       
        if(is_null($Value))
       {
            return $this->Description;
       }
       elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
       {
            $this->Description = $Value;
       }
    }

    public function FilePath($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->File_Path;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_file($Value))
            {
                $this->File_Path = $Value;
            }
        }
    }

    public function LineNumber($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->File_Line_Number;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->File_Line_Number = $Value;
            }
        }
    }    

    public function ClassName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        $caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        if(is_null($Value))
        {
            return $this->Class_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            $this->Class_Name = $Value;
        }
    }

    public function FunctionName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->Function_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            $this->Function_Name = $Value;
        }
    }

    private function CheckIfWritten(string $Method_Name)
    {
        $wrote_key = array_search($Method_Name, $this->Written_Flags, true);
        if($wrote_key === false)
        {
            $this->Written_Flags[] = $Method_Name;
            return false;
        }
        else
        {
            return true;
        }
    }

    // # - Object -> Array
    public function ToArray()
    {
        $key_name_list = array_column($this->Object_Config['DataItems'], "data_name");
        $value_list = array_column($this->Object_Config['DataItems'], "value");
        $items = array_combine($key_name_list, $value_list);
        return $items;
    }

    public function CheckRequiredFields()
    {
        $return_flag = true;
        $this->Required_Flags = array();
        if(isset($this->Object_Config['DataItems']) and is_array($this->Object_Config['DataItems']))
        {
            foreach($this->Object_Config['DataItems'] as $config_line)
            {
                if(isset($config_line['required_flag']) and $config_line['required_flag'] === true)
                {
                    $this->Required_Flags[$config_line['data_name']] = false;
                    if(isset($config_line['value']) and $config_line['value'] !== "")
                    {
                        $this->Required_Flags[$config_line['data_name']] = true;
                    }
                }
            }
            if(in_array(false, $this->Required_Flags, true) === true )
            {
                $return_flag = false;
            }
        }
        return $return_flag;
    }

}