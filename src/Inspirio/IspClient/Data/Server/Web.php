<?php
namespace Inspirio\IspClient\Data\Server;

use Inspirio\IspClient\DataObject;

class Web extends DataObject
{
	public $websiteBasedir;
	public $websitePath;
	public $websiteSymlinks;
	public $vhostConfDir;
	public $vhostConfEnabledDir;
	public $securityLevel;
	public $checkApacheConfig;
	public $user;
	public $group;
	public $phpIniPathApache;
	public $phpIniPathCgi;
	public $phpOpenBasedir;
	public $htaccessAllowOverride;
	public $appsVhostPort;
	public $appsVhostIp;
	public $appsVhostServername;
	public $awstatsConfDir;
	public $awstatsDataDir;
	public $awstatsPl;
	public $awstatsBuildstaticpagesPl;
}