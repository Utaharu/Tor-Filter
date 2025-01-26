<?php
namespace Sora_Kanata\Tor_Filter\Lib\Autoload;
use Sora_Kanata\Tor_Filter\Lib\Error\Error_Manager;

$caller = debug_backtrace();
$project_loader = new Project_Loader($caller[0]['file']);

class Project_Loader
{
    private string $Vender_Name = "";
    private string $Project_Name = "";
    private string $Base_Name_Space = "";
    private string $Base_Root = "";
    private object $Error_Manager;

    function __construct(string $Script_Path)
    {
        list($this->Vender_Name, $this->Project_Name) = explode("\\", __NAMESPACE__);
        $this->Base_Name_Space = $this->Vender_Name . "\\" . $this->Project_Name;
        $this->Base_Root = dirname($Script_Path); 
        spl_autoload_register(__CLASS__ . "::ScriptRequire");
        $this->Error_Manager = new Error_Manager();
    }

    /* # This NameSpace AutoLoader # */
    private function ScriptRequire($class_name)
    {
        if(preg_match("/^" . preg_quote($this->Base_Name_Space, "/") .  "/i", $class_name))
        {
            $file_path = preg_replace("/^" . preg_quote($this->Base_Name_Space, "/") ."\\\\(.+)/i", $this->Base_Root . '\\\\$1.php' , $class_name);
            $file_path = mb_strtolower($file_path);
            $file_path = strtr($file_path, "\\", "/");
            if(is_file($file_path) and file_exists($file_path))
            {
                require_once($file_path);
            }
            else
            {
                if(isset($this->Error_Manager))
                {
                    $this->Error_Manager->SetLog(basename($file_path) . "のAutoloadに失敗しました。");
                    $this->Error_Manager->PrintLastError();
                }
            }
        }
        return;
    }

}