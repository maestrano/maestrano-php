<?php

class MaestranoTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->config = MaestranoTestHelper::getConfig();
    }

    public function testConfigurationBinding()
    {
        $marketplace = 'same-marketplace';
        Maestrano::with($marketplace)->configure($this->config['marketplaces'][0]);

        $this->assertEquals($this->config['marketplaces'][0]['nid'], Maestrano::with($marketplace)->param('nid'));
        $this->assertEquals($this->config['marketplaces'][0]['marketplace'], Maestrano::with($marketplace)->param('marketplace'));
        $this->assertEquals($this->config['marketplaces'][0]['environment'], Maestrano::with($marketplace)->param('environment'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['host'], Maestrano::with($marketplace)->param('app.host'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_start_path'], Maestrano::with($marketplace)->param('app.synchronization_start_path'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_toggle_path'], Maestrano::with($marketplace)->param('app.synchronization_toggle_path'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_status_path'], Maestrano::with($marketplace)->param('app.synchronization_status_path'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['id'], Maestrano::with($marketplace)->param('api.id'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['key'], Maestrano::with($marketplace)->param('api.key'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['host'], Maestrano::with($marketplace)->param('api.host'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['base'], Maestrano::with($marketplace)->param('api.base'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['idm'], Maestrano::with($marketplace)->param('sso.idm'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['init_path'], Maestrano::with($marketplace)->param('sso.init_path'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['consume_path'], Maestrano::with($marketplace)->param('sso.consume_path'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['idp'], Maestrano::with($marketplace)->param('sso.idp'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['x509_fingerprint'], Maestrano::with($marketplace)->param('sso.x509_fingerprint'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['x509_certificate'], Maestrano::with($marketplace)->param('sso.x509_certificate'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['host'], Maestrano::with($marketplace)->param('connec.host'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['base_path'], Maestrano::with($marketplace)->param('connec.base_path'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['timeout'], Maestrano::with($marketplace)->param('connec.timeout'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['account']['group_path'], Maestrano::with($marketplace)->param('webhooks.account.group_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['account']['group_user_path'], Maestrano::with($marketplace)->param('webhooks.account.group_user_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['external_ids'], Maestrano::with($marketplace)->param('webhooks.connec.external_ids'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['initialization_path'], Maestrano::with($marketplace)->param('webhooks.connec.initialization_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['notification_path'], Maestrano::with($marketplace)->param('webhooks.connec.notification_path'));
    }

    public function testConfigurationFromFile()
    {
        $this->setExpectedException('Maestrano_Config_Error');

        $path = "config.json";
        file_put_contents($path, json_encode($this->config['marketplaces'][0]));

        Maestrano::configure($path);

        unlink($path);
    }

    public function testAuthenticateWhenValid()
    {
        $preset = 'some-marketplace';
        Maestrano::with($preset)->configure($this->config['marketplaces'][0]);

        $this->assertTrue(Maestrano::with($preset)->authenticate($this->config['marketplaces'][0]['api']['id'], $this->config['marketplaces'][0]['api']['key']));
    }

    public function testAuthenticateWhenInvalid()
    {
        $preset = 'some-marketplace';
        Maestrano::with($preset)->configure($this->config['marketplaces'][0]);

        $this->assertFalse(Maestrano::with($preset)->authenticate($this->config['marketplaces'][0]['api']['id'] . "aaa", $this->config['marketplaces'][0]['api']['key']));
        $this->assertFalse(Maestrano::with($preset)->authenticate($this->config['marketplaces'][0]['api']['id'], $this->config['marketplaces'][0]['api']['key'] . "aaa"));
    }

    public function testPresetWithNullPreset()
    {
        $this->setExpectedException('Maestrano_Config_Error', 'Empty preset name, make sure you are using \'Maestrano::with($marketplace)->someMethod()\'');

        Maestrano::paramWithPreset(null, 'api.key');
    }

    public function testPresetWithEmptyStringPreset()
    {
        $this->setExpectedException('Maestrano_Config_Error', 'Empty preset name, make sure you are using \'Maestrano::with($marketplace)->someMethod()\'');

        Maestrano::paramWithPreset('', 'api.key');
    }

    public function testPresetNotConfigured()
    {
        $this->setExpectedException('Maestrano_Config_Error', "Maestrano was not configured for preset 'another-marketplace'");

        Maestrano::with('some-marketplace')->configure($this->config['marketplaces'][0]);
        Maestrano::paramWithPreset('another-marketplace', 'api.key');
    }

    public function testBindingConfigurationWithPreset()
    {
        $preset = 'some-marketplace';
        Maestrano::with($preset)->configure($this->config['marketplaces'][0]);

        $this->assertEquals($this->config['marketplaces'][0]['nid'], Maestrano::with($preset)->param('nid'));
        $this->assertEquals($this->config['marketplaces'][0]['marketplace'], Maestrano::with($preset)->param('marketplace'));
        $this->assertEquals($this->config['marketplaces'][0]['environment'], Maestrano::with($preset)->param('environment'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['host'], Maestrano::with($preset)->param('app.host'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_start_path'], Maestrano::with($preset)->param('app.synchronization_start_path'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_toggle_path'], Maestrano::with($preset)->param('app.synchronization_toggle_path'));
        $this->assertEquals($this->config['marketplaces'][0]['app']['synchronization_status_path'], Maestrano::with($preset)->param('app.synchronization_status_path'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['id'], Maestrano::with($preset)->param('api.id'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['key'], Maestrano::with($preset)->param('api.key'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['host'], Maestrano::with($preset)->param('api.host'));
        $this->assertEquals($this->config['marketplaces'][0]['api']['base'], Maestrano::with($preset)->param('api.base'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['idm'], Maestrano::with($preset)->param('sso.idm'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['init_path'], Maestrano::with($preset)->param('sso.init_path'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['consume_path'], Maestrano::with($preset)->param('sso.consume_path'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['idp'], Maestrano::with($preset)->param('sso.idp'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['x509_fingerprint'], Maestrano::with($preset)->param('sso.x509_fingerprint'));
        $this->assertEquals($this->config['marketplaces'][0]['sso']['x509_certificate'], Maestrano::with($preset)->param('sso.x509_certificate'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['host'], Maestrano::with($preset)->param('connec.host'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['base_path'], Maestrano::with($preset)->param('connec.base_path'));
        $this->assertEquals($this->config['marketplaces'][0]['connec']['timeout'], Maestrano::with($preset)->param('connec.timeout'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['account']['group_path'], Maestrano::with($preset)->param('webhooks.account.group_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['account']['group_user_path'], Maestrano::with($preset)->param('webhooks.account.group_user_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['external_ids'], Maestrano::with($preset)->param('webhooks.connec.external_ids'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['initialization_path'], Maestrano::with($preset)->param('webhooks.connec.initialization_path'));
        $this->assertEquals($this->config['marketplaces'][0]['webhooks']['connec']['notification_path'], Maestrano::with($preset)->param('webhooks.connec.notification_path'));
    }

    public function testToMetadata()
    {
        $preset = 'some-marketplace';
        Maestrano::with($preset)->configure($this->config['marketplaces'][0]);

        $expected = array(
            'nid' => $this->config['marketplaces'][0]['nid'],
            'marketplace' => $this->config['marketplaces'][0]['marketplace'],
            'environment' => $this->config['marketplaces'][0]['environment'],
            'app' => array(
                'host' => $this->config['marketplaces'][0]['app']['host'],
                'synchronization_start_path' => $this->config['marketplaces'][0]['app']['synchronization_start_path'],
                'synchronization_toggle_path' => $this->config['marketplaces'][0]['app']['synchronization_toggle_path'],
                'synchronization_status_path' => $this->config['marketplaces'][0]['app']['synchronization_status_path']
            ),
            'api' => array(
                'id' => $this->config['marketplaces'][0]['api']['id'],
                'key' => $this->config['marketplaces'][0]['api']['key'],
                'host' => $this->config['marketplaces'][0]['api']['host'],
                'base' => $this->config['marketplaces'][0]['api']['base'],
                'version' => Maestrano::VERSION,
                'lang' => 'php',
                'lang_version' => phpversion() . " " . php_uname(),
            ),
            'sso' => array(
                'idm' => $this->config['marketplaces'][0]['sso']['idm'],
                'init_path' => $this->config['marketplaces'][0]['sso']['init_path'],
                'consume_path' => $this->config['marketplaces'][0]['sso']['consume_path'],
                'idp' => $this->config['marketplaces'][0]['sso']['idp'],
            ),
            'connec' => array(
                'host' => $this->config['marketplaces'][0]['connec']['host'],
                'base_path' => $this->config['marketplaces'][0]['connec']['base_path'],
                'timeout' => $this->config['marketplaces'][0]['connec']['timeout']
            ),
            'webhooks' => array(
                'account' => array(
                    'group_path' => $this->config['marketplaces'][0]['webhooks']['account']['group_path'],
                    'group_user_path' => $this->config['marketplaces'][0]['webhooks']['account']['group_user_path'],
                ),
                'connec' => array(
                    'external_ids' => $this->config['marketplaces'][0]['webhooks']['connec']['external_ids'],
                    'initialization_path' => $this->config['marketplaces'][0]['webhooks']['connec']['initialization_path'],
                    'notification_path' => $this->config['marketplaces'][0]['webhooks']['connec']['notification_path']
                )
            )
        );

        $this->assertEquals(json_encode($expected), Maestrano::with($preset)->toMetadata());
    }
}
