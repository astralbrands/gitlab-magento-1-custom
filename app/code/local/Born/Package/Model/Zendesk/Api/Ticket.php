<?php 

class Born_Package_Model_Zendesk_Api_Ticket extends Born_Package_Model_Zendesk_Api_Abstract
{
	private $commentId;
	private $ticketId;
	private $attachmentToken;

	protected function setTicketId($id)
	{
		if ($this->ticketId) {
			return false;
		}

		$this->ticketId = $id;

		return true;
	}

	public function getAttachmentToken()
	{
		return $this->attachmentToken;
	}


	public function create($data)
	{
		$_url = 'tickets.json';
		$_json = json_encode($data);
		$_action = 'POST';

		$result = $this->curlWrap($_url, $_json, $_action);

		//Save ticket Id

		if ($_ticketId = $result->ticket->id)
		{
			$this->setTicketId($_ticketId);
		}

		return $result;
	}
	
	public function addAttachments()
	{
		$_attachmentIds = $this->attachmentIds;

		foreach ($_attachmentIds as $_id) {
			$this->addAttachment($_id);
		}
	}

	protected function addAttachment($_attachmentId)
	{

		//PUT /api/v2/tickets/{ticket_id}/comments/{comment_id}/attachments/{attachment_id}/redact.json
		
		$_ticketId = $this->ticketId;
		$_commendId = $this->commentId;
		$_currentClass = get_class($this);

		if (!$_ticketId) {
			//Mage::log($_currentClass . '::addAttachment - Ticket Id Not Found');
			return;
		}
		if (!$_commendId) {
			//Mage::log($_currentClass . '::addAttachment - Comment Id Not Found');
			return;
		}
		if (!$_attachmentId) {
			//Mage::log($_currentClass . '::addAttachment - Attachment Id Not Found');
			return;
		}

		$_action = 'PUT';
		$_url = 'tickets/';
		$_url .= $ticketId .'/';
		$_url .= 'comment/';
		$_url .= $_commendId . '/';
		$_url .= 'attachments/';
		$_url .= $_attachmentId . '/';
		$_url .= 'redact.json';

		$result = $this->curlWrap($_url, null, $_action);
	}
	
	
	public function upload($file)
	{
		$_name = $file['file'];
		$_path = $file['path'] . $file['file'];

		$result = $this->curlUpload($_name, $_path);

		if($_attachmentToken = $result->upload->token)
		{
			$this->attachmentToken = $_attachmentToken;
		}

		return $result;
	}

	public function curlUpload($fileName, $filePath)
	{
		$userName = $this->getUserName();
		$apiKey = $this->getApiKey();
		$apiUrl =$this->getApiUrl();

		$token = $this->getAttachmentToken(); //use for multiple file attachments

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

	public function prepareTicketData($postData)
	{
		
		if(!$postData)
		{
			//Mage::log(get_class($this) . '::prepareTicketData - No Post Form Data');
			return;
		}

		$zenHelper = Mage::Helper('born_package/zendesk_data');

		$_name = $postData['first_name'];

		if ($_name && $postData['last_name']) {
			$_name .= ' ' . $postData['last_name'];
		}

		$_email = $postData['email_address'];

		$_subject = $zenHelper->getFormName() ? $zenHelper->getFormName() : 'Professional Signup';

		$_body = $zenHelper->prepareTicketBody($postData);

		$data['ticket'] = array(
			'requester' => array(
				'name' => $_name, 
				'email' => $_email, 
				),
			'subject' => $_subject,
			'comment' => array(
				'html_body' => $_body,
				),
			);

		if ($_attachmentToken = $this->getAttachmentToken()) {
			$data['ticket']['comment']['uploads'] = $_attachmentToken;
		}

		if ($_brandId = $zenHelper->getBrandId()) {
			$data['ticket']['brand_id'] = $_brandId;
		}

		return $data;
	}
}
?>