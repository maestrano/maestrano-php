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
      $this->config = array(
        'environment' => 'production',
        'app' => array(
          'host' => "https://mysuperapp.com",
        ),
        'api' => array(
          'id' => "myappid",
          'key' => "myappkey",
          'group_id' => "mygroupid",
          'host' => 'https://someapihost.com'
        ),
        'sso' => array(
          'init_path' => "/mno/init_path.php",
          'consume_path' => "/mno/consume_path.php",
          'idp' => "https://mysuperidp.com",
          'idm' => "https://mysuperidm.com",
          'x509_fingerprint' => "some-x509_fingerprint",
          'x509_certificate' => "some-x509_certificate"
        ),
        'connec' => array(
          'enabled' => true,
          'host' => 'http://connec.maestrano.io',
          'base_path' => '/api',
          'v2_path' => '/v2',
          'reports_path' => '/reports'
        ),
        'webhook' => array(
          'account' => array(
            'groups_path' => "/mno/groups/:id",
            'group_users_path' => "/mno/groups/:group_id/users/:id"
          ),
          'connec' => array(
            'enabled' => true,
            'initialization_path' => "/mno/connec/initialization",
            'notifications_path' => "/mno/connec/notifications",
            'subscriptions' => array(
              'organizations' => true,
              'people' => true
            )
          )
        )
      );
      $preset = 'some-marketplace';
      Maestrano::with($preset)->configure($this->config);
      $this->ssoService = Maestrano::ssoWithPreset($preset);
    }


    public function testAttributeParsing() {
  		$this-> assertEquals(true, $this->ssoService->isSsoEnabled());
      $this-> assertEquals(true, $this->ssoService->isSloEnabled());
      $this-> assertEquals('/mno/init_path.php', $this->ssoService->getInitPath());
      $this-> assertEquals('https://mysuperapp.com/mno/init_path.php', $this->ssoService->getInitUrl());
      $this-> assertEquals('/mno/consume_path.php', $this->ssoService->getConsumePath());
      $this-> assertEquals('https://mysuperapp.com/mno/consume_path.php', $this->ssoService->getConsumeUrl());
      $this-> assertEquals('https://mysuperidp.com/app_logout', $this->ssoService->getLogoutUrl());
      $this-> assertEquals('https://someapihost.com/app_access_unauthorized', $this->ssoService->getUnauthorizedUrl());
      $this-> assertEquals('https://mysuperidp.com/api/v1/auth/saml', $this->ssoService->getIdpUrl());
      $this-> assertEquals('https://mysuperidp.com/api/v1/auth/saml/user?session=token', $this->ssoService->getSessionCheckUrl('user', 'token'));
    }
}
?>
