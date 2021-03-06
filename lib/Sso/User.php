<?php

/**
 * Properly format a User received from Maestrano SAML IDP
 */
class Maestrano_Sso_User
{
    /* UID of current group */
    public $groupUid = '';

    /* Role in current group */
    public $groupRole = '';

    /* User UID */
    public $uid = '';

    /* User Virtual UID - unique across users and groups */
    public $virtualUid = '';

    /* User email */
    public $email = '';

    /* User virtual email - unique across users and groups */
    public $virtualEmail = '';

    /* User firstName */
    public $firstName = '';

    /* User lastName */
    public $lastName = '';

    /* User country - alpha2 code */
    public $country = '';

    /* User company firstName */
    public $companyName = '';

    /* Maestrano specific user sso session token */
    public $ssoSession = '';

    /* When to recheck for validity of the sso session */
    public $ssoSessionRecheck = null;

    /**
     * Construct the Maestrano_Sso_User object from a SAML response
     * @param $saml_response Maestrano_Saml_Response A SamlResponse object from Maestrano
     */
    public function __construct($saml_response)
    {
        // Get assertion attributes
        $att = $saml_response->getAttributes();

        // Group related information
        $this->groupUid = $att['group_uid'];
        $this->groupRole = $att['group_role'];

        // Extract mno session information
        $this->ssoSession = $att['mno_session'];
        $this->ssoSessionRecheck = new DateTime($att['mno_session_recheck']);

        // Extract user metadata
        $this->uid = $att['uid'];
        $this->virtualUid = $att['virtual_uid'];
        $this->virtualEmail = $att['virtual_email'];
        $this->email = $att['email'];
        $this->firstName = $att['name'];
        $this->lastName = $att['surname'];
        $this->country = $att['country'];
        $this->companyName = $att['company_name'];
    }

    /**
     * The Maestrano user ID (UID)
     * @return string user ID (UID)
     */
    public function getId() {
        return $this->uid;
    }

    /**
     * The Maestrano user UID
     * @return string user UID
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * The user virtual (ID) UID which is truly unique across users and groups
     * @return string user virtual uid
     */
    public function getVirtualId() {
        return $this->virtualUid;
    }

    /**
     * The user virtual UID which is truly unique across users and groups
     * @return string user virtual uid
     */
    public function getVirtualUid() {
        return $this->virtualUid;
    }

    /**
     * The actual user email
     * @return string user email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Virtual email that can be used instead of regular email fields
     * This email is unique across users and groups
     * Do not use this email address to send emails to the user
     * @return string virtual email
     */
    public function getVirtualEmail() {
        return $this->virtualEmail;
    }

    /**
     * Return the current user session token
     * @return string session token
     */
    public function getSsoSession() {
        return $this->ssoSession;
    }

    /**
     * Return when the user session should be remotely checked
     * @return DateTime session check time
     */
    public function getSsoSessionRecheck() {
        return $this->ssoSessionRecheck;
    }

    /**
     * Return the user group UID
     * @return string group UID
     */
    public function getGroupUid() {
        return $this->groupUid;
    }

    /**
     * Return the user role in the group
     * Roles are: 'Member', 'Power User', 'Admin', 'Super Admin'
     * @return string user role in group
     */
    public function getGroupRole() {
        return $this->groupRole;
    }

    /**
     * User first firstName
     * @return string user first name
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * User last last name
     * @return string user last name
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * ALPHA2 code of user country
     * @return string country
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Company firstName entered by the user
     * Can be empty
     * @return string company name
     */
    public function getCompanyName() {
        return $this->companyName;
    }
}
