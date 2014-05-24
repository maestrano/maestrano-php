<?php

/**
 * Helper class used to check the validity
 * of a Maestrano session
 */
class Maestrano_Sso_Session
{
  /**
   * Session object
   */
  public $session = null;
  
  /**
   * User UID
   */
  public $uid = '';
  
  /**
   * Maestrano SSO token
   */
  public $token = '';
  
  /**
   * When to recheck for validity of the sso session
   */
  public $recheck = null;
  
  /**
   * Construct the Maestrano_Sso_Session object
   */
  public function __construct(& $http_session)
  {
      // Populate attributes from params
      $this->session = &$http_session;
      $this->uid = $this->session['mno_uid'];
      $this->token = $this->session['mno_session'];
      $this->recheck = new DateTime($this->session['mno_session_recheck']);
  }
  
  /**
   * Check whether we need to remotely check the
   * session or not
   *
   * @return boolean
   */
   public function remoteCheckRequired()
   {
     if ($this->uid && $this->token && $this->recheck) {
       if($this->recheck > (new DateTime('NOW'))) {
         return false;
       }
     }
     
     return true;
   }
   
   /**
    * Return the full url from which session check
    * should be performed
    *
    * @return string the endpoint url
    */
    public function getSessionCheckUrl()
    {
      $url = Maestrano::sso()->getSessionCheckUrl($this->uid,$this->token);
      return $url;
    }
    
    /**
     * Fetch url and return content. Wrapper function.
     *
     * @param string full url to fetch
     * @return string page content
     */
    public function fetchUrl($url) {
      return file_get_contents($url);
    }
    
    /**
     * Perform remote session check on Maestrano
     *
     * @return boolean the validity of the session
     */
     public function performRemoteCheck() {
       $json = $this->fetchUrl($this->getSessionCheckUrl());
       if ($json) {
        $response = json_decode($json,true);
        
        if ($response['valid'] && $response['recheck']) {
          $this->recheck = new DateTime($response['recheck']);
          return true;
        }
       }
       
       return false;
     }
     
     /**
      * Perform check to see if session is valid
      * Check is only performed if current time is after
      * the recheck timestamp
      * If a remote check is performed then the mno_session_recheck
      * timestamp is updated in session.
      *
      * @return boolean the validity of the session
      */
      public function isValid() {
        if ($this->remoteCheckRequired()) {
          if ($this->performRemoteCheck()) {
            $this->session['mno_session_recheck'] = $this->recheck->format(DateTime::ISO8601);
            return true;
          } else {
            return false;
          }
        } else {
          return true;
        }
      }
}