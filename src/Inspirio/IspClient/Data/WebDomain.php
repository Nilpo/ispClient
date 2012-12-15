<?php
namespace Inspirio\IspClient\Data;

use Inspirio\IspClient\DataObject;

class WebDomain extends DataObject
{
	const TYPE_ALIAS     = 'alias';
	const TYPE_SUBDOMAIN = 'subdomain';
	const TYPE_VHOST     = 'vhost';

	const PHP_DISABLED = 'no';
	const PHP_MODPHP   = 'mod';
	const PHP_SUPHP    = 'suphp';

	public $id;
	public $serverId;
	public $ipAddress;
	public $domain;
	public $type;
	public $parentDomainId;
	public $vhostType;
	public $documentRoot;
	public $systemUser;
	public $systemGroup;
	public $hdQuota;
	public $trafficQuota;
	public $cgi;
	public $ssi;
	public $suexec;
	public $errordocs;
	public $isSubdomainwww;
	public $subdomain;
	public $php;
	public $ruby;
	public $redirectType;
	public $redirectPath;
	public $ssl;
	public $sslState;
	public $sslLocality;
	public $sslOrganisation;
	public $sslOrganisationUnit;
	public $sslCountry;
	public $sslDomain;
	public $sslRequest;
	public $sslCert;
	public $sslBundle;
	public $sslAction;
	public $statsPassword;
	public $statsType;
	public $allowOverride;
	public $apacheDirectives;
	public $phpOpenBasedir;
	public $customPhpIni;
	public $backupInterval;
	public $backupCopies;
	public $active;
	public $trafficQuotaLock;
}
