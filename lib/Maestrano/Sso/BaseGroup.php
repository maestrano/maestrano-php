<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class Maestrano_Sso_BaseGroup
{
  /* User UID */
  public $uid = '';
  
  /* When does free trial terminate */
  public $free_trial_end_at = '';
  
  /* Company Name */
  public $company_display_name = '';
  
  /* Country - alpha2 format */
  public $country = '';
  
  /* Group Local Id */
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
      // Get assertion attributes and session
      $assert_attrs = $saml_response->getAttributes();
      $this->session = Maestrano::sso()->getHttpSession();
      
      // Extract session information
      $this->uid = $assert_attrs['group_uid'][0];
      $this->free_trial_end_at = new DateTime($assert_attrs['group_end_free_trial'][0]);
      $this->country = $assert_attrs['country'][0];
      $this->company_name = $assert_attrs['company_name'][0];
  }
  
  /**
   * Try to find a local group matching the sso one
   * using uid.
   * ---
   * Internally use the following interface methods:
   *  - getLocalIdByUid
   * 
   * @return local_id if a local user matched, null otherwise
   */
  public function matchLocal()
  {
    // Try to get the local id from uid
    $this->local_id = $this->getLocalIdByUid();
    
    return $this->local_id;
  }
  
  /**
   * Return wether the group was matched or not
   * Check if the local_id is null or not
   * 
   * @return boolean
   */
  public function isMatched()
  {
    return !is_null($this->local_id);
  }
  
  /**
   * Create a local group (global customer account) by invoking createLocalGroup
   * and set uid on the newly created group
   */
   public function createLocalGroupAndMatch()
   {
     if (is_null($this->local_id)) {
       $this->local_id = $this->createLocalGroup();

        // If a group has been created successfully
        // then make sure UID is set on it
        if ($this->local_id) {
          $this->setLocalUid();
        }
     }
     
     return $this->local_id;
   }
  
   /**
    * Add a user to an existing group if the user is not
    * part of it already
    */
   public function addUser($sso_user,$user_role) {
     throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_Group class!');
   }
  
  /**
   * Create a local group based on the sso user
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return a group ID if created, null otherwise
   */
  protected function createLocalGroup()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_Group class!');
  }
  
  /**
   * Get the ID of a local group via Maestrano UID lookup
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return a group ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_Group class!');
  }
  
  /**
   * Set the Maestrano UID on a local group
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return boolean
   */
  protected function setLocalUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Maestrano_Sso_Group class!');
  }
  
}