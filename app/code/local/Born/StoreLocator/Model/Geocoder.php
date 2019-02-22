<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Model_Geocoder {

    private $_url = "https://maps.googleapis.com/maps/api/geocode/json?key=%s&sensor=false";

    
    /**
    * converts components to google api format
    * 
    * @param mixed $components array(country,postal_code)
    */
    private function _prepareComponents($components) {
        $out=array();
        foreach($components as $key => $component) {
            if(!$component)
                continue;
            
            $out[]="$key:$component";
        }
        
        return implode('|',$out);

    }
    
    /**
    * 
    * https://developers.google.com/maps/documentation/geocoding/?hl=en#ComponentFiltering
    * The components that can be filtered include:

    route matches long or short name of a route. 
    locality matches against both locality and sublocality types.
    administrative_area matches all the administrative_area levels.
    postal_code matches postal_code and postal_code_prefix.
    country matches a country name or a two letter ISO 3166-1 country code.
    * 
    * @param mixed $address
    * @param mixed $country
    * @param mixed $zip
    * @return stdClass
    */

    public function getLocation($address,$language=null){

        if ($key = Mage::getStoreConfig('storelocator/storelocator/google_api_key')) {
            //$key = 'AIzaSyA3XFSXOY9wuwesA2-xDvpOW7tduS-GGIw';
            $url = sprintf($this->_url, $key);

            //https://developers.google.com/maps/documentation/geocoding/#ComponentFiltering
            if(trim($address))
                $url .= '&address='.urlencode($address); 

            if($language)
                $url.="&language=$language";

            ////Mage::log($url);

            $resp_json = self::curl_file_get_contents($url);
            $resp = json_decode($resp_json, true);
            if(!$resp) {
                throw new Exception("Wrong Json Response: ". strip_tags($resp_json));
            }
            if (!empty($resp['error_message'])) {
                throw new Mage_Core_Exception($resp['error_message']);
            }
            //return full response
            return $resp;

        } else 
            throw new Mage_Core_Exception('Please specify Google Geo API Key: System -> Configuration -> Catalog -> Store Locator');
    }


    static private function curl_file_get_contents($URL){

        $apikey = Mage::getStoreConfig('storelocator/storelocator/google_api_key');

        $header = array('Content-Type: text/xml','ApiKey: '.$apikey,'Accept: application/xml');
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //   curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        curl_close($ch);

        if(!$output)
            //Mage::log('GeoCoder Curl Error: '. curl_error($ch),Zend_Log::DEBUG);

        return $output;
    }
}
