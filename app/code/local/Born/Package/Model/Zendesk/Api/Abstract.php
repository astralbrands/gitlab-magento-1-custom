<?php 

class Born_Package_Model_Zendesk_Api_Abstract
{
	private $userName;
	private $apiKey;
	private $apiUrl;

	protected function getUserName()
	{
		if (!$this->userName) {
			$path = 'zendesk/general/email';

			$this->userName = $this->getConfig($path);
		}

		return $this->userName;
	}

	protected function getApiKey()
	{
		if (!$this->apiKey) {
			$path = 'zendesk/general/password';

			$this->apiKey = $this->getConfig($path);
		}

		return $this->apiKey;
	}

	protected function getApiUrl()
	{
		if (!$this->apiUrl) {
			$path = 'zendesk/general/domain';
			$url = $this->getConfig($path);
			if ($url) {
				$this->apiUrl = 'https://' . $url . '/api/v2/';
			}
		}

		return $this->apiUrl;
	}

	protected function getConfig($path)
	{
		$_storeId = Mage::app()->getStore()->getStoreId();

		$_config = Mage::getStoreConfig($path, $_storeId);

		return $_config;
	}
	public function curlWrap($url, $json, $action)
	{
		$ch = curl_init();

		$_userName = $this->getUserName();
		$_apiKey = $this->getApiKey();
		$_apiUrl = $this->getApiUrl();

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_URL, $_apiUrl . $url);
		curl_setopt($ch, CURLOPT_USERPWD, $_userName ."/token:". $_apiKey);

		switch($action){
			case "POST":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
			case "GET":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			break;
			case "PUT":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
			case "DELETE":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
			default:
			break;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($output);
		return $decoded;
	}
}
?>