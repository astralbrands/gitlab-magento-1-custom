<?php 

class Born_Package_Helper_Persistent_Customer_Form_Professional extends Born_Package_Helper_Config
{
	public function getCreateUrl()
	{
		$_urlKey = $this->getCreateUrlKey();

		if (!$_urlKey) {
			return;
		}

		$_professionalUrl = Mage::getUrl($_urlKey);

		if ($_professionalUrl && !strpos($_professionalUrl, 'SID') === false) {
			//Remove session id if url has it
			$_professionalUrl = Mage::getModel('core/url')->sessionUrlVar($_professionalUrl);
		}

		$_anchorTag = $this->getCreateAnchorTag();

		if ($_anchorTag) {
			if (strpos($_professionalUrl, '#') === false) {
				$_anchorTag = '#' . $_anchorTag;
			}
			$_professionalUrl .= $_anchorTag;
		}

		return $_professionalUrl;

	}

	public function getCreateUrlKey()
	{
		return $this->getConfig('customer/startup/professional_signup_url_key');
	}

	public function getCreateAnchorTag()
	{
		return $this->getConfig('customer/startup/professional_signup_anchor_tag');	
	}
}

 ?>