<?php
class ErrorAnswer
{
    public $message;
    private $status;
    public function __construct($message, $status)
    {
        $this->message = $message;
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
