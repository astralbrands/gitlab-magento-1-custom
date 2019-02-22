<?php

/**
 * Astal Brand
 *
 * @category    Astal Brand
 * @package     Born_Package
 * @date        23/01/2016
 * @author      tanuja
 * @description  Preparing the ticket data.
 */
class Born_Package_Model_Zendesk_Api_BloggerTicket extends Born_Package_Model_Zendesk_Api_Abstract
{
    private $ticketId;

    protected function setTicketId($id)
    {
        if ($this->ticketId) {
            return false;
        }

        $this->ticketId = $id;
        Mage:
        log('ticket id ' . $id);

        return true;
    }

    public function create($data)
    {
        $_url = 'tickets.json';
        $_json = json_encode($data);
        $_action = 'POST';

        $result = $this->curlWrap($_url, $_json, $_action);

        //Save ticket Id

        if ($_ticketId = $result->ticket->id) {
            $this->setTicketId($_ticketId);
        }

        return $result;
    }

    public function prepareTicketData($postData)
    {

        if (!$postData) {
            //Mage::log(get_class($this) . '::prepareTicketData - No Post Form Data');
            return;
        }

        $zenHelper = Mage::Helper('born_package/zendesk_data');

        $_name = $postData['first_name'];

        $_email = $postData['email_address'];

        $_subject = $zenHelper->getAffiliateFormName() ? $zenHelper->getAffiliateFormName() : 'Blogger Signup';

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


        if ($_brandId = $zenHelper->getBrandId()) {
            $data['ticket']['brand_id'] = $_brandId;
        }

        return $data;
    }
}

?>