<?php

namespace cfg\app\services;

use cfg\app\services\File;

/**
 * Description of Mail
 *
 * @author Kiala
 */
class Mail {

    private $mail_type;
    private $dest;
    private $destname;
    private $subject;
    private $message;
    private $file;
    private $mailoptions;

    //Types des messages
    const MAIL_TEXT = 1;
    const MAIL_HTML = 2;
    const MAIL_MULTIPART_TEXT = 3;
    const MAIL_MULTIPART_HTML = 4;

    private function getDelimiter() {
        return md5(uniqid(mt_rand()));
    }

    public function setMailType($type = self::MAIL_TEXT) {

        $this->mail_type = $type;
        return $this;
    }

    public function getMailType() {

        return $this->mail_type;
    }

    public function setFile(File $file) {
        $this->file = $file;
        return $this;
    }

    public function getFile() {
        return $this->file;
    }

    public function setDest($dest) {
        $this->dest = $dest;
        return $this;
    }

    public function getDest() {
        return $this->dest;
    }
    
    public function setDestname($destname) {
        $this->destname = $destname;
        return $this;
    }
    
    public function getDestname() {
        return $this->destname;
    }
    
    public function completeName($dest) {
        return $this->destname . "<" . $dest . ">";
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function getMessage() {
        return $this->message;
    }

    public function send() {

        $mail = null;

        switch ($this->getMailType()) {
            case self::MAIL_HTML :
                $mail = $this->mailHTML();
                break;
            case self::MAIL_TEXT :
                $mail = $this->mailText();
                break;
            case self::MAIL_MULTIPART_TEXT :
                $mail = $this->mailTextMultipart();
                break;
            case self::MAIL_MULTIPART_HTML :
                $mail = $this->mailHTMLMultipart();
                break;
            default :
        }

        try {
            $this->execute($mail[0], $mail[1], $mail[2], $mail[3], $mail[4]);
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }

        return true;
    }

    private function execute($to, $subject, $message, $headers = null, $options = null) {

        if (mail($to, $subject, $message, $headers, $options)) {
            return true;
        } else {
            throw new Exception("Le mail ne peut être envoyé");
        }
    }

    private function validMail() {
        if (filter_var($this->dest, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            throw new \Exception("L'adresse email n'est pas correcte");
        }

        return false;
    }

    private function mailText() {
        $headers = "From:machine@ntp.org";
        $headers .= "Reply-To:machine@ntp.org";

        try {
            $this->validMail();
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }

        return $this->mailOptions($this->dest, $this->subject, $this->message, $headers, null);
    }

    private function mailHTML() {

        $headers = $this->getHTMLHeader();

        try {
            $this->validMail();
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }

        return $this->mailOptions($this->dest, $this->subject, $this->message, $headers, null);
    }

    private function mailTextMultipart() {

        $filetype   = $this->file->getType('file');
        $filename   = $this->file->getFilename('file');
        $message    = $this->message;
        $file       = file_get_contents($this->file->getTemp('file'));
        $content    = chunk_split(base64_encode($file));
        $uid        = md5(uniqid(time())); 

        // build the headers for attachment and html
        $headers = "From: machine@ntp.org\r\n";
        $headers .= "Reply-To: machine@ntp.org\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $headers .= "This is a multi-part message in MIME format.\r\n";
        $headers .= "--".$uid."\r\n";
        $headers .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $headers .= $message."\r\n\r\n";
        $headers .= "--".$uid."\r\n";
        $headers .= "Content-Type: ".$filetype."; name=\"" . $filename . "\"\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $headers .= "Content-Disposition: attachment; filename=\"". $filename ."\"\r\n\r\n";
        $headers .= $content."\r\n\r\n";
        $headers .= "--".$uid."--";

        return $this->mailOptions($this->dest, $this->subject, $message, $headers, null);
    }

    private function mailHTMLMultipart() {
        
        $filetype   = $this->file->getType('file');
        $filename   = $this->file->getFilename('file');
        $message    = $this->message;
        $file       = file_get_contents($this->file->getTemp('file'));
        $content    = chunk_split(base64_encode($file));
        $uid        = md5(uniqid(time())); 

        // build the headers for attachment and html
        $headers = "From: machine@ntp.org\r\n";
        $headers .= "Reply-To: machine@ntp.org\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $headers .= "This is a multi-part message in MIME format.\r\n";
        $headers .= "--".$uid."\r\n";
        $headers .= "Content-type:text/html; charset=iso-8859-1\r\n";
        $headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $headers .= $message."\r\n\r\n";
        $headers .= "--".$uid."\r\n";
        $headers .= "Content-Type: ".$filetype."; name=\"" . $filename . "\"\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $headers .= "Content-Disposition: attachment; filename=\"". $filename ."\"\r\n\r\n";
        $headers .= $content."\r\n\r\n";
        $headers .= "--".$uid."--";

        return $this->mailOptions($this->dest, $this->subject, $message, $headers, null);
    }
    
    private function mailOptions($dest, $subject, $message, $headers, $options) {
        
        $this->mailoptions = array(
            $this->completeName($dest), $subject, strip_tags($message), str_replace("\r\n","\n",$headers), $options
        );

        return $this->mailoptions;
    }

    private function getHTMLHeader() {

        $header = "MIME-Version: 1.0";
        $header .= "Content-Type:text/html;charset=utf-8\n";
        $header .= "Content-Transfer-Encoding: 8bits\n";
        $header .= "From:machine@ntp.org";
        $header .= "Reply-To:machine@ntp.org";

        return $header;
    }

    private function compose($content) {

        $delimiter = $this->getDelimiter();
        $head = "MIME-Version: 1.0\n";
        $head .= "Content-Type:multipart/mixed; boundady=" . $delimiter . " \n";
        $head .= "\n";

        $msg = "--{$delimiter}\n";

        $msg .= "Content-Type: text/html; charset=\"utf-8\"\n";
        $msg .= "Content-Transfer-Encoding:8bit\n";

        $msg .= "\n";
        $msg .= $content;
        $msg .= "\n";

        $msg = "--{$delimiter}\n";

        $msg .= "Content-Type: image/gif; name=\"{}\"";
        $msg .= "\n";

        $msg .= $this->attachFile($file) . "\n";
        $msg .= "\n";

        $msg .= "--{$delimiter}--\n";
    }

    private function attachFile($file) {

        return chunk_split(base64_encode($file['temp_name']));
    }

    public function __construct() {
        $this->setFile(new File());
    }
}
