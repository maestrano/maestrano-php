<?php

class MockRequest extends \Httpful\Request {

    private $raw_body;

    public $isAuthenticateWithCalled = false;
    public $isUriCalled = false;
    public $isSendCalled = false;

    public function __construct()
    {
        $this->raw_body;
    }

    public function uri($url) {
        $this->isUriCalled = true;
        return $this;
    }

    public function authenticateWith($key, $secret) {
        $this->isAuthenticateWithCalled = true;
        return $this;
    }

    public function send() {
        $this->isSendCalled = true;

        return $this;
    }
}