<?php

/**
 * Properly format a Group received from Maestrano SAML IDP
 */
class Maestrano_Sso_Group
{
    private $uid;
    private $name;
    private $email;
    private $hasCreditCard;
    private $freeTrialEndAt;
    private $companyName;
    private $currency;
    private $timezone;
    private $country;
    private $city;
    private $mainAccounting;

    /**
     * Constructor
     * @param samlResponse a SAML Response from Maestrano IDP
     * @throws ParseException
     */
    public function __construct($saml_response)
    {
        $att = $saml_response->getAttributes();

        // General info
        $this->uid = $att["group_uid"];
        $this->name = $att["group_name"];
        $this->email = $att["group_email"];
        $this->freeTrialEndAt = new DateTime($att["group_end_free_trial"]);
        $this->companyName = $att["company_name"];
        $this->hasCreditCard = ($att["group_has_credit_card"] != null && $att["group_has_credit_card"] == "true");

        // Geo info
        $this->currency = $att["group_currency"];
        $this->timezone = new DateTimeZone($att["group_timezone"]);
        $this->country = $att["group_country"];
        $this->city = $att["group_city"];
    }

    /**
     * Return the group ID (UID)
     * @return string group ID (UID)
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * Return the group UID
     * @return string group UID
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Return the name of the group
     * @return string group name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the principal contact email for this group
     * @return string principal email address
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return whether the group has a credit card
     * @return string user entered a cc or not
     */
    public function hasCreditCard()
    {
        return $this->hasCreditCard;
    }

    /**
     * Return when the group free trial is finishing
     * @return DateTime end of free trial
     */
    public function getFreeTrialEndAt()
    {
        return $this->freeTrialEndAt;
    }

    /**
     * Return the original company name for this group (can be empty)
     * @return string company name
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Return the currency code of main currency used by this group
     * @return string currency code
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Return the timezone for this group
     * @return TimeZone group timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Return the ALPHA2 country code for this group
     * @return string alpha2 country code
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Return the city in which this group is located
     * @return string group city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Return the main accounting package used by this
     * group
     * @return string accounting package
     */
    public function getMainAccounting()
    {
        return $this->mainAccounting;
    }

}
