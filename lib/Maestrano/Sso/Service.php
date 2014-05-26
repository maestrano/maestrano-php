<?php

/**
 * SSO Service
 */
class Maestrano_Sso_Service
{
  /* Singleton instance */
  protected static $_instance;
  
  /* Path to redirect to after signin */
  protected $after_sso_sign_in_path = '/';
  
  /* Pointer to the current client session */
  protected $client_session;
  
  /**
   * Returns an instance of this class
   * (this class uses the singleton pattern)
   *
   * @return Maestrano_Sso_Service
   */
  public static function instance()
  {
      if ( ! isset(self::$_instance)) {
          self::$_instance = new self();
      }
      return self::$_instance;
  }

  /**
  * Return the maestrano sso session
  *
  * @return Maestrano_Sso_Session
  */
  public function session(& $http_session)
  {
    return new Maestrano_Sso_Session($http_session);
  }
  
  /**
  * Return a new saml request object
  *
  * @param Array of GET parameters (optional)
  * @return Maestrano_Saml_Request instance
  */
  public function buildRequest($get_params = array())
  {
    return new Maestrano_Saml_Request($get_params);
  }
  
  /**
  * Return a new saml response object
  *
  * @param String saml_response
  * @return Maestrano_Saml_Request instance
  */
  public function buildResponse($saml_response)
  {
    return new Maestrano_Saml_Response($saml_response);
  }

  /**
   * Check if Maestrano SSO is enabled
   *
   * @return boolean
   */
   public function isSsoEnabled()
   {
     return Maestrano::param('sso_enabled');
   }

  /**
   * Return where the app should redirect internally to initiate
   * SSO request
   *
   * @return boolean
   */
  public function getInitUrl()
  {
    $host = Maestrano::param('app_host');
    $path = Maestrano::param('sso_app_init_path');
    return "${host}${path}";
  }
  
  /**
   * The URL where the SSO response will be posted and consumed.
   * @var string
   */
  public function getConsumeUrl()
  {
    $host = Maestrano::param('app_host');
    $path = Maestrano::param('sso_app_consume_path');
    return "${host}${path}";
  }

  /**
   * Return where the app should redirect after logging user
   * out
   *
   * @return string url
   */
  public function getLogoutUrl()
  {
    $host = Maestrano::param('api_host');
    $endpoint = '/app_logout';
    
    return "${host}${endpoint}";
  }

  /**
   * Return where the app should redirect if user does
   * not have access to it
   *
   * @return string url
   */
  public function getUnauthorizedUrl()
  {
    $host = Maestrano::param('api_host');
    $endpoint = '/app_access_unauthorized';
    
    return "${host}${endpoint}";
  }

  /**
   * Set the after sso signin path
   *
   * @return string url
   */
  public function setAfterSignInPath($path)
  {
    $this->$after_sso_sign_in_path = $path;
  }

  /**
   * Return the after sso signin path
   *
   * @return string url
   */
  public function getAfterSignInPath()
  {
  	return $this->after_sso_sign_in_path;
  }
  
  /**
   * Maestrano Single Sign-On processing URL
   * @var string
   */
  public function getIdpUrl() {
    $host = Maestrano::param('api_host');
    $api_base = Maestrano::param('api_base');
    $endpoint = 'auth/saml';
    return "${host}${api_base}${endpoint}";
  }
  
  /**
   * The Maestrano endpoint in charge of providing session information
   * @var string
   */
  public function getSessionCheckUrl($user_id,$sso_session) 
  {
    $host = Maestrano::param('api_host');
    $api_base = Maestrano::param('api_base');
    $endpoint = 'auth/saml';
    
    return "${host}${api_base}${endpoint}/${user_id}?session=${sso_session}";
  }
  
  /**
   * Return a settings object for php-saml
   * 
   * @return Maestrano_Saml_Settings
   */
  public function getSamlSettings() {
    $settings = new Maestrano_Saml_Settings();
    
    // Configure SAML
    $settings->idpPublicCertificate = Maestrano::param('sso_x509_certificate');
    $settings->spIssuer = Maestrano::param('api_key');
    $settings->requestedNameIdFormat = Maestrano::param('sso_name_id_format');
    $settings->idpSingleSignOnUrl = $this->getIdpUrl();
    $settings->spReturnUrl = $this->getConsumeUrl();
    
    return $settings;
  }
}