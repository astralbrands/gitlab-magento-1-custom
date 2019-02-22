<?php 

class Born_Package_Model_Zendesk_Api_Attachment extends Born_Package_Model_Zendesk_Api_Abstract
{

	private $uploadCollection;

	public function __construct() {
		 parent::_construct();
		 $this->uploadCollection = new Varien_Data_Collection();
	}

	public function create($data)
	{
		$_url = 'tickets.json';
		$_json = json_encode($data);
		$_action = 'POST';

		$result = $this->curlWrap($_url, $_json, $_action);

		return $result;
	}
	
	public function upload($file)
	{
		$_name = $file->getName();
		$_path = $file->getPath();

		$result = $this->curlUpload($_name, $_path);

		if (count($result['attachments']) > 0) {
			foreach ($result['attachments'] as $attachment) {
				$file->setId($attachment['id']);
				$file->setContentUrl($attachment['content_url']);
			}
		}

		return $result;
	}

	public function curlUpload($fileName, $filePath)
	{
		// basic settings for your Zendesk
		$userName = $this->getUserName();
		$apiKey = $this->getApiKey();
		$apiUrl =$this->getApiUrl();

		$token = NULL; // set to previously returned token to upload multiple files in 1 comment

		$url = $apiUrl . 'uploads.json?filename='.urlencode($fileName);

		$url .= (is_null($token)) ? '' : '&token='.$token;
		$file = fopen($filePath, "r");
		$size = filesize($filePath);
		$fildata = fread($file,$size);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERPWD, $userName."/token:".$apiKey);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		$headers = array('Content-Type: application/binary', 'Accept: application/json; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
		curl_setopt($ch, CURLOPT_HEADER, 0); // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $fildata);

		curl_setopt($ch, CURLOPT_INFILE, $file);
		curl_setopt($ch, CURLOPT_INFILESIZE, $size);

		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		$output = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		fclose($file);
		curl_close($ch);

		if ($code !== 200 && $code !== 201)
		{
			return 'Status code returned was '.$code.'!';
		}

		$decoded = json_decode($output);

		return $decoded;
		}
	}
?>