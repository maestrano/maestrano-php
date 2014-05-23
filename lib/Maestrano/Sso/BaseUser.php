<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class Maestrano_Sso_BaseUser
{
  /* Session Object */
  public $session = null;
  
  /* Role in current group */
  public $group_role = '';
  
  /* User UID */
  public $uid = '';
  
  /* User Virtual UID - unique across users and groups */
  public $virtual_uid = '';
  
  /* User email */
  public $email = '';
  
  /* User virtual email - unique across users and groups */
  public $virtual_email = '';
  
  /* User name */
  public $name = '';
  
  /* User surname */
  public $surname = '';
  
  /* User country - alpha2 code */
  public $country = '';
  
  /* User company name */
  public $company_name = '';
  
  /* Maestrano specific user sso session token */
  public $sso_session = '';
  
  /* When to recheck for validity of the sso session */
  public $sso_session_recheck = null;
  
  /**
   * User Local Id
   * @var string
   */
  public $local_id = null;
  
  
  /**
   * Construct the Maestrano_Sso_BaseUser object from a SAML response
   *
   * @param Maestrano_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(Maestrano_Saml_Response $saml_response)
  {
      // Get assertion attributes
      $assert_attrs = $saml_response->getAttributes();
      
      // Group related information
      $this->group_uid  = $assert_attrs['group_uid'][0];
      $this->group_role = $assert_attrs['group_role'][0];
      
      // Extract session information
      $this->session = &Maestrano::sso()->getHttpSession(); #reference
      $this->sso_session = $assert_attrs['mno_session'][0];
      $this->sso_session_recheck = new DateTime($assert_attrs['mno_session_recheck'][0]);
      
      // Extract user metadata
      $this->uid = $assert_attrs['uid'][0];
      $this->virtual_uid = $assert_attrs['virtual_uid'][0];
      $this->email = $assert_attrs['email'][0];
      $this->virtual_email = $assert_attrs['virtual_email'][0];
      $this->name = $assert_attrs['name'][0];
      $this->surname = $assert_attrs['surname'][0];
      $this->country = $assert_attrs['country'][0];
      $this->company_name = $assert_attrs['company_name'][0];
  }
  
  /* 
   * Result depends on Maestranos#user_creation_mode:
   * 'real': return the real maestrano uid (set this if users can be part of multiple groups)
   * 'virtual': return a composite maestrano uid (set this if users can only be part of one group)
   */
  public function getUid() {
    if (Maestrano::param('user_creation_mode') == 'real') {
      return $this->uid;
    } else {
      return $this->virtual_uid;
    }
  }
  
  /* 
   * Result depends on Maestranos#user_creation_mode:
   * 'real': return the real maestrano email (set this if users can be part of multiple groups)
   * 'virtual': return a composite maestrano email (set this if users can only be part of one group)
   */
  public function getEmail() {
    if (Maestrano::param('user_creation_mode') == 'real') {
      return $this->email;
    } else {
      return $this->virtual_email;
    }
  }
  
  /**
   * Try to find a local application user matching the sso one
   * using uid first, then email address.
   * 
   * @return local_id if a local user matched, null otherwise
   */
  public function matchLocal()
  {
    // Try to get the local id from uid
    $this->local_id = $this->getLocalIdByUid();
    
    // Sync local details if we have a match
    if ($this->local_id) {
      $this->syncLocalDetails();
    }
    
    return $this->local_id;
  }
  
  /**
   * Return wether the user was matched or not
   * Check if the local_id is null or not
   * 
   * @return boolean
   */
  public function isMatched()
  {
    return !is_null($this->local_id);
  }
  
  
  /**
   * Create a local user by invoking createLocalUser
   * and set uid on the newly created user
   * If createLocalUser returns null then access
   * is refused to the user
   */
   public function createLocalUserOrDenyAccess()
   {
     if (is_null($this->local_id)) {
       $this->local_id = $this->createLocalUser();

        // If a user has been created successfully
        // then make sure UID is set on it
        if ($this->local_id) {
          $this->setLocalUid();
        }
     }
     
     return $this->local_id;
   }
  
  /**
   * Create a local user based on the sso user
   * This method must be re-implemented in Maestrano_Sso_User
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function createLocalUser()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_User class!');
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   * This method must be re-implemented in Maestrano_Sso_User
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_User class!');
  }
  
  /**
   * Get the ID of a local user via email lookup
   * This method must be re-implemented in Maestrano_Sso_User
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_User class!');
  }
  
  /**
   * Set the Maestrano UID on a local user via email lookup
   * This method must be re-implemented in Maestrano_Sso_User
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_User class!');
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * This is a convenience method that must be implemented in
   * Maestrano_Sso_User but is not mandatory.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     return true;
   }
  
  /**
   * Sign the user in the application. By default,
   * set the mno_uid, mno_session and mno_session_recheck
   * in session.
   * It is expected that this method get extended with
   * application specific behavior in the Maestrano_Sso_User class
   *
   * @return boolean whether the user was successfully signedIn or not
   */
  public function signIn()
  {
    if ($this->setInSession()) {
      $this->session['mno_uid'] = $this->uid;
      $this->session['mno_session'] = $this->sso_session;
      $this->session['mno_session_recheck'] = $this->sso_session_recheck->format(DateTime::ISO8601);
    }
  }
  
  /**
   * Generate a random password.
   * Convenient to set dummy passwords on users
   *
   * @return string a random password
   */
  protected function generatePassword()
  {
    $length = 20;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }
  
  /**
   * Set user in session. Called by signIn method.
   * This method should be overriden in Maestrano_Sso_User to
   * reflect the app specific way of putting an authenticated
   * user in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
   protected function setInSession()
   {
     throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_User class!');
   }
}