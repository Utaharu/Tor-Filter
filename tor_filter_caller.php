<?php
require_once dirname(__FILE__) . "/lib/autoload/project_loader.php";

$tor_filter  = new Sora_Kanata\Tor_Filter\Tor_Filter();
if($tor_filter->IsFunctionEnabled())
{
    // - # Check - Access User IpAddress
    $tor_address_flag = false;
    $tor_address_flag = $tor_filter->IsTorIpAddress();
    if($tor_address_flag)
    {
        $tor_filter->Error("Tor_Filter Error", 'Tor経由でのアクセスは禁止されています。');
    }
}