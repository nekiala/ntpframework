<?php

namespace cfg\app\services;

/**
 * Description of SMS
 *
 * @author Kiala
 */
class SMS {

    //put your code here
    private $url;
    private $sender;
    private $client_code;
    private $pass_code;
    private $message;
    private $msisdn;
    private $fields = array();
    private $field_string;
    
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function setMsisdn($msisdn) {
        $this->msisdn = $msisdn;
        return $this;
    }
    
    public function getMsisdn() {
        return $this->msisdn;
    }
    
    private function configMsg() {
        
        $smsData = "<DATA><MESSAGE><![CDATA[" . $this->getMessage() . "]]></MESSAGE><TPOA>" .$this->getSender() ."</TPOA><SMS><MOBILEPHONE>". $this->getMsisdn() . "</MOBILEPHONE></SMS></DATA>";
        return $smsData;
    }

    public function config() {
        
        $json = \cfg\app\Application::APP_FILE;
        $config = json_decode($json, 1);
        $sms_api = $config['api']['sms'];
        
        $this->url = $sms_api['url'];
        $this->client_code = $sms_api['ccode'];
        $this->pass_code = $sms_api['passwd'];
        $this->sender = $sms_api['passwd'];
        $smsData = $this->configMsg();

        $this->fields = array(
            'clientcode' => urlencode($this->client_code),
            'passcode' => urlencode($this->pass_code),
            'smsData' => urlencode($smsData),
        );

        $this->field_string = "";
        foreach ($this->fields as $key => $value) {
            $this->field_string .= $key . '=' . $value . '&';
        }
        rtrim($this->field_string, '&');
    }
    
    public function send() {
        
        try {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POST, count($this->fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->field_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            echo $result;

            curl_close($ch);
            
        } catch (Exception $e) {
            
            echo 'Api allmysms injoignable ou trop longue a repondre ' . $e->getMessage();
        }
    }

}
