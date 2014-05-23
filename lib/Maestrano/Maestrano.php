<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class Maestrano
{
  // Maestrano PHP API Version
  const VERSION = '0.1';

  /* Environment: 'test' or 'production' */
  protected static $environment = 'production';

  /* Internal Config Map */
  protected static $config = array();
  
  /**
  * Configure Maestrano API
  *
  * @return true
  */
  public static function configure($settings)
  {
    if (array_key_exists('environment', $settings)) {
      self::$environment = $settings['environment'];
    } else {
      trigger_error("No environment provided. Defaulting to: '" . self::$environment . "'",E_USER_NOTICE);
    }
    
    if (array_key_exists('api_key', $settings)) {
      self::$config['api_key'] = $settings['api_key'];
    } else {
      throw new ArgumentException('No api_key provided. Please add your API key.');
    }
    
    if (array_key_exists('sso_enabled', $settings)) {
      self::$config['sso_enabled'] = $settings['sso_enabled'];
    } else {
      self::$config['sso_enabled'] = true;
    }
    
    if (array_key_exists('app_host', $settings)) {
      self::$config['app_host'] = $settings['app_host'];
    } else {
      self::$config['app_host'] = 'http://localhost:8888';
      trigger_error("No application host provided. Defaulting to: '" . self::$config['app_host'] . "'",E_USER_NOTICE);
    }
    
    if (array_key_exists('sso_app_init_path', $settings)) {
      self::$config['sso_app_init_path'] = $settings['sso_app_init_path'];
    } else {
      self::$config['sso_app_init_path'] = '/maestrano/auth/saml/index.php';
    }
    
    if (array_key_exists('sso_app_consume_path', $settings)) {
      self::$config['sso_app_consume_path'] = $settings['sso_app_consume_path'];
    } else {
      self::$config['sso_app_consume_path'] = '/maestrano/auth/saml/consume.php';
    }
    
    if (array_key_exists('user_creation_mode', $settings)) {
      self::$config['user_creation_mode'] = $settings['user_creation_mode'];
    } else {
      self::$config['sso_app_consume_path'] = 'virtual';
    }
    
    // Check SSL certificate on API requests
    if (array_key_exists('verify_ssl_certs', $settings)) {
      self::$config['verify_ssl_certs'] = $settings['verify_ssl_certs'];
    } else {
      self::$config['verify_ssl_certs'] = false;
    }
    
    // string|null The version of the Maestrano API to use for requests.
    if (array_key_exists('api_version', $settings)) {
      self::$config['api_version'] = $settings['api_version'];
    } else {
      self::$config['api_version'] = null;
    }
    
    return true;
  }
  
   
   /**
    * Return a configuration parameter
    */
   public static function param($parameter) {
     if (array_key_exists($parameter, self::$config)) {
       return self::$config[$parameter];
     } else if (array_key_exists($parameter, self::$evt_config[self::$environment])) {
       return self::$evt_config[self::$environment][$parameter];
     } else {
       throw new ArgumentException("No such configuration parameter: '". $parameter ."'");
     }
   }
   
   /**
    * Return the SSO service
    * 
    * @return Maestrano_Sso_Service singleton
    */
   public static function sso() {
     return Maestrano_Sso_Service::instance();
   }
  
  
    /* 
    * Environment related configuration 
    */
    private static $evt_config = array(
    'test' => array(
      'api_host'               => 'http://api-sandbox.maestrano.io',
      'api_base'               => '/api/v1/',
      'sso_name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso_x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5\nWhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu\nP2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I\n5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2\n28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo\neHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ\njYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc\nMPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc\n2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES\nRkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==\n-----END CERTIFICATE-----"
    ),
    'production' => array(
      'api_host'               => 'https://maestrano.com',
      'api_base'               => '/api/v1/',
      'sso_name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso_x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAPFpcH2rW0pyMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyNDEw\nWhcNMzMxMjMwMDUyNDEwWjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD3feNNn2xfEz5/\nQvkBIu2keh9NNhobpre8U4r1qC7h7OeInTldmxGL4cLHw4ZAqKbJVrlFWqNevM5V\nZBkDe4mjuVkK6rYK1ZK7eVk59BicRksVKRmdhXbANk/C5sESUsQv1wLZyrF5Iq8m\na9Oy4oYrIsEF2uHzCouTKM5n+O4DkwIDAQABo4HuMIHrMB0GA1UdDgQWBBSd/X0L\n/Pq+ZkHvItMtLnxMCAMdhjCBuwYDVR0jBIGzMIGwgBSd/X0L/Pq+ZkHvItMtLnxM\nCAMdhqGBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA8WlwfatbSnIwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQDE\nhe/18oRh8EqIhOl0bPk6BG49AkjhZZezrRJkCFp4dZxaBjwZTddwo8O5KHwkFGdy\nyLiPV326dtvXoKa9RFJvoJiSTQLEn5mO1NzWYnBMLtrDWojOe6Ltvn3x0HVo/iHh\nJShjAn6ZYX43Tjl1YXDd1H9O+7/VgEWAQQ32v8p5lA==\n-----END CERTIFICATE-----"
    )
    );
}