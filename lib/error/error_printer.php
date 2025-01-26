<?php
namespace Sora_Kanata\Tor_Filter\Lib\Error;

class Error_Printer
{
	public function Print ($Error_Title, $Error_Message)
	{
		print "<html>\n";
		print "	<head>\n";
		print "		<title>{$Error_Title}</title>\n";
		print "	</head>\n";
		print "	<body>\n";
		print "	<div style=\"color:red;text-align:center;font-size:18px;font:bold;\">\n";
		print "		{$Error_Title}\n";
		print "	</div>\n";
		print "	<p style=\"text-align:center;\">\n";
		print "		{$Error_Message}\n";
		print "	</p>\n";
		print "</html>";

		exit;
	}
}