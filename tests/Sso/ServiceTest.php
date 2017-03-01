<?php

/**
 * Unit tests for Maestrano_Sso_Service
 */
class Maestrano_Sso_ServiceTest extends PHPUnit_Framework_TestCase
{
    protected $config;
    protected $ssoService;

    /**
     * Initializes the Test Suite
     */
    public function setUp()
    {
        $this->config = MaestranoTestHelper::getConfig();
        $preset = 'some-marketplace';
        Maestrano::with($preset)->configure($this->config['marketplaces'][0]);

        $this->ssoService = Maestrano::ssoWithPreset($preset);
    }

    public function testAttributeParsing() {
        $this->assertEquals('/maestrano/auth/saml/init.php?marketplace=some-marketplace', $this->ssoService->getInitPath());
        $this->assertEquals('http://php-demoapp.maestrano.dev/maestrano/auth/saml/init.php?marketplace=some-marketplace', $this->ssoService->getInitUrl());
        $this->assertEquals('/maestrano/auth/saml/consume.php?marketplace=some-marketplace', $this->ssoService->getConsumePath());
        $this->assertEquals('http://php-demoapp.maestrano.dev/maestrano/auth/saml/consume.php?marketplace=some-marketplace', $this->ssoService->getConsumeUrl());
        $this->assertEquals('https://api-hub.maestrano.com/app_logout?user_uid=uid-fd45s', $this->ssoService->getLogoutUrl('uid-fd45s'));
        $this->assertEquals('https://api-hub.maestrano.com/app_access_unauthorized', $this->ssoService->getUnauthorizedUrl());
        $this->assertEquals('https://api-hub.maestrano.com/api/v1/auth/saml', $this->ssoService->getIdpUrl());
        $this->assertEquals('https://api-hub.maestrano.com/api/v1/auth/saml/user?session=token', $this->ssoService->getSessionCheckUrl('user', 'token'));
    }
}
