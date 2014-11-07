<?php

class MaestranoTest extends PHPUnit_Framework_TestCase
{
    protected $config;
  
    protected function setUp()
    {
      $this->config = array(
        'environment' => 'production',
        'app' => array(
          'host' => "https://mysuperapp.com",
        ),
        'api' => array(
          'id' => "myappid",
          'key' => "myappkey",
        ),
        'sso' => array(
          'init_path' => "/mno/init_path.php",
          'consume_path' => "/mno/consume_path.php",
        ),
        'webhook' => array(
          'account' => array(
            'groups_path' => "/mno/groups/:id",
            'group_users_path' => "/mno/groups/:group_id/users/:id"
          )
        )
      );
      
      
    }
    
    public function testBindingConfiguration() {
      Maestrano::configure($this->config);
      
      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
    }
    
    public function testConfigurationFromFile() {
      $path = "config.json";
      file_put_contents($path,json_encode($this->config));
      
      Maestrano::configure($path);
      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
      
      unlink($path);
    }
}


?>