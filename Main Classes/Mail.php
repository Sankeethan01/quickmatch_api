<?php

class Mail
{
    private $to;
    private $subject;
    private $message;
    private $headers;

    public function __construct($to, $subject, $message, $from = 'noreply@example.com')
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = 'From: ' . $from . "\r\n" .
                         'Reply-To: ' . $from . "\r\n" .
                         'X-Mailer: PHP/' . phpversion();
    }

    public function send()
    {
        if (mail($this->to, $this->subject, $this->message, $this->headers)) {
            return true;
        } else {
            return false;
        }
    }

    public function addCC($cc)
    {
        $this->headers .= 'Cc: ' . $cc . "\r\n";
    }

    public function addBCC($bcc)
    {
        $this->headers .= 'Bcc: ' . $bcc . "\r\n";
    }

    public function setHTML($isHTML = true)
    {
        if ($isHTML) {
            $this->headers .= 'MIME-Version: 1.0' . "\r\n";
            $this->headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        }
    }
}
