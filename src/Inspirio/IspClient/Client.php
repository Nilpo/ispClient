<?php
namespace Inspirio\IspClient;

class Client
{
	const SERVER_INFO_WEB = 'web';

	/**
	 * @var SoapClient
	 */
	private $server;

	/**
	 * @var mixed
	 */
	private $sessionId;

	/**
	 * @var Monolog\Logger
	 */
	private $log;

	/**
	 * Constructor.
	 *
	 * @param string $server the server URL
	 */
	public function __construct($server, \Monolog\Logger $log)
	{
		$soap_location = "{$server}/remote/index.php";
		$soap_uri      = "{$server}/remote/";

		$this->server = new \SoapClient(null, array(
			'location'   => $soap_location,
			'uri'        => $soap_uri,
			'trace'      => 1,
			'exceptions' => 1,
		));

		$this->sessionId = null;
		$this->log       = $log;
	}

	/**
	 * Maps the received data item to an object.
	 *
	 * @param array $list
	 * @param string $itemClass
	 */
	private function mapReceivedData(array $arrayList, $itemClass)
	{
		$objectList = array();

		foreach ($arrayList as $item) {
			$objectList[] = $itemClass::fromData($item);
		}

		return $objectList;
	}

	/**
	 * Authenticates as given user.
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login($username, $password)
	{
		$this->sessionId = $this->server->login($username, $password);

		return $this->sessionId != null;
	}

	/**
	 * Logs out the current user.
	 *
	 */
	public function logout()
	{
		// no user logged in
		if ($this->sessionId === null) {
			return;
		}

		$this->server->logout($this->sessionId);
	}

	/**
	 * Returns the server info.
	 *
	 * @param int $serverId
	 * @param string $section self::SERVER_INFO_*
	 * @return DataObject
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getServerInfo($serverId, $section)
	{
		switch ($section) {
			case self::SERVER_INFO_WEB:
				$dataObject = 'Web';
				break;

			// TODO implement other sections

			default:
				throw new \InvalidArgumentException("Invalid \$section parameter value '{$section}'.");
		}

		$data = $this->server->server_get($this->sessionId, $serverId, $section);

		$dataObject = __NAMESPACE__.'\\Data\\Server\\'.$dataObject;
		return $dataObject::fromData($data);
	}

	/**
	 * Returns the list of the clients.
	 *
	 * @return Data\Client[]
	 */
	public function getClientList()
	{
		$clients = $this->server->client_get($this->sessionId, array(
			'parent_client_id' => 0
		));

		return $this->mapReceivedData($clients, 'Data\\Client');
	}

	/**
	 * Returns the client.
	 *
	 * @return IspData\Client
	 */
	public function getClient($clientId)
	{
		$data = $this->server->client_get($this->sessionId, $clientId);

		return Data\Client::fromData($data);
	}

	/**
	 * Adds new domain.
	 *
	 * @param Data\Domain $domain
	 */
	public function addDomain(Data\Domain $domain)
	{
		$data = $domain->toData();

		$clientId = $data['client_id'];
		unset($data['client_id']);

		$domainId = $this->server->domains_domain_add($this->sessionId, $clientId, $data);

		$this->log->addInfo("Created new domain {$domain->domain}[{$domainId}]");

		$domain->domainId = $domainId;
	}

	/**
	 * Deletes the domain.
	 *
	 * @param int $domainId
	 * @return bool
	 */
	public function deleteDomain($domainId)
	{
		$deletedCount = $this->server->domains_domain_delete($this->sessionId, $domainId);

		if ($deletedCount == 1) {
			$this->log->addInfo("Deleted domain [{$domainId}]");
			return true;

		} else {
			$this->log->addInfo("Failed to delete domain [{$domainId}]");
			return false;
		}
	}

	/**
	 * Adds new website.
	 *
	 * @param Data\WebDomain $website
	 * @return int new website id
	 */
	public function addWebsite(Data\WebDomain $website)
	{
		/* @var $webInfo Data\Server\Web */
		$webInfo = $this->getServerInfo($website->serverId, self::SERVER_INFO_WEB);

		$website->applyDefaultValues(array(
			'documentRoot'   => $webInfo->websitePath,
			'phpOpenBasedir' => $webInfo->phpOpenBasedir,
			'systemUser'     => $webInfo->user,
			'systemGroup'    => $webInfo->group,
			'type'           => Data\WebDomain::TYPE_VHOST,
			'vhostType'      => 'name',
			'ipAddress'      => '*',
			'parentDomainId' => 0,
			'hdQuota'        => -1,
			'trafficQuota'   => -1,
			'cgi'            => 'n',
			'ssi'            => 'n',
			'suexec'         => 'n',
			'errordocs'      => 'n',
			'subdomain'      => '*',
			'php'            => 'n',
			'ruby'           => 'n',
			'allowOverride'  => 'all',
			'active'         => 'y',
		));

		$data = $website->toData();

		$clientId = $data['client_id'];
		unset($data['client_id']);

		$id = $this->server->sites_web_domain_add($this->sessionId, $clientId, $data);
		$website->id = $id;

		$this->log->addInfo("Created new website {$website->domain}[{$id}]");

		return $id;
	}

	/**
	 * Deletes the website.
	 *
	 * @param int $websiteId
	 * @return bool
	 */
	public function deleteWebsite($websiteId)
	{
		$deletedCount = $this->server->sites_web_domain_delete($this->sessionId, $websiteId);

		if ($deletedCount == 1) {
			$this->log->addInfo("Deleted website [{$domainId}]");
			return true;

		} else {
			$this->log->addInfo("Failed to delete website [{$domainId}]");
			return false;
		}
	}

	/**
	 * Adds new website alias.
	 *
	 * @param Data\WebDomain $websiteAlias
	 * @return int website alias id
	 */
	public function addWebsiteAlias(Data\WebDomain $websiteAlias)
	{
		// apply the default values
		$webInfo = $this->getServerInfo($websiteAlias->serverId, self::SERVER_INFO_WEB); /* @var $webInfo Data\Server\Web */

		$websiteAlias->applyDefaultValues(array(
			'documentRoot'   => $webInfo->websitePath,
			'phpOpenBasedir' => $webInfo->phpOpenBasedir,
			'systemUser'     => $webInfo->user,
			'systemGroup'    => $webInfo->group,
			'type'           => Data\WebDomain::TYPE_ALIAS,
			'hdQuota'        => -1,
			'trafficQuota'   => -1,
			'subdomain'      => '*',
			'allowOverride'  => 'all',
			'active'         => 'y',
		));

		$data = $websiteAlias->toData();

		$clientId = $data['client_id'];
		unset($data['client_id']);

		$id = $this->server->sites_web_domain_add($this->sessionId, $clientId, $data);
		$websiteAlias->id = $id;

		$this->log->addInfo("Created new website alias {$websiteAlias->domain}[{$id}]");

		return $id;
	}
}
