<?php
namespace Sora_Kanata\Tor_Filter\Lib\Converter;

use Sora_Kanata\Tor_Filter\Lib\Error\Error_Manager;
use Sora_Kanata\Tor_Filter\Lib\File\File_Manager;

class Source_Data_Converter
{
    private array $Data_List = array();
    private array $DataList_ArrayOrder = array();
    private string $Extract_MatchPattern = "";
    private object $Error_Manager;
 
    function __construct(string $Extract_MatchPattern, array $Extracted_ArrayOrder = array())
    {
        if($Extract_MatchPattern)
        {
            $this->Extract_MatchPattern = $Extract_MatchPattern;
        }
        if(is_array($Extracted_ArrayOrder) and count($Extracted_ArrayOrder) > 0)
        {
            $this->DataList_ArrayOrder = $Extracted_ArrayOrder;
        }
        $this->Data_List = array();
        $this->Error_Manager = new Error_Manager();
    }

    public function ExtractStringToArray(string $Data)
    {
        if($this->Extract_MatchPattern and preg_match_all($this->Extract_MatchPattern, $Data, $extracted_list, PREG_SET_ORDER) > 0)
        {
            foreach($extracted_list as $extracted_items)
            {
                if($extracted_items)
                {
                    $ordered_extract_items = array();
                    if(is_array($this->DataList_ArrayOrder) and count($this->DataList_ArrayOrder) > 0)
                    {
                        foreach($this->DataList_ArrayOrder as $order_key_name)
                        {
                            if($order_key_name and array_key_exists($order_key_name, $extracted_items))
                            {
                                $ordered_extract_items[$order_key_name] = $extracted_items[$order_key_name];
                            }
                        }
                    }
                    else
                    {
                        $ordered_extract_items = $extracted_items;
                    }
                }
                $this->Data_List[] = $ordered_extract_items;
            }
        }

        return;
    }

    public function GetDataList()
    {
        return $this->Data_List;
    }
}