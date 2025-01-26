<?php
namespace Sora_Kanata\Tor_Filter\Lib\User;

class User_Manager
{
    private string $Ip_Address = "";
    private string $Host_Name = "";

    function __construct($User_IpAddress = "")
    {
        $this->SetupIpAddressProparty();
        if($User_IpAddress)
        {
            $this->Ip_Address = $User_IpAddress;
        }
    }

	private function SetupIpAddressProparty()
	{
		$this->Ip_Address = "";
        $this->Host_Name = "";
		$this->Ip_Address = getenv('REMOTE_ADDR');
		$this->Host_Name  = getenv('REMOTE_HOST');		
		if(!$this->Host_Name  or $this->Host_Name == $this->Ip_Address)
        {
            $this->Host_Name  = gethostbyaddr($this->Ip_Address);
        }
		if(!$this->Ip_Address or $this->Host_Name == $this->Ip_Address)
        {
            $this->Ip_Address = gethostbyname($this->Host_Name);
        }
		
		if(!$this->Host_Name and $this->Ip_Address)
        {
            $this->Host_Name = $this->Ip_Address;
        }
		if(!$this->Ip_Address and $this->Host_Name)
        {
            $this->Ip_Address = $this->Host_Name;
        }
		
		return;
	}

    public function IpAddress()
    {
        return $this->Ip_Address;
    }
}