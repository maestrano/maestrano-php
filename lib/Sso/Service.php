<?php

/**
 * SSO Service
 */
class Maestrano_Sso_Service extends Maestrano_Util_PresetObject
{
    /* Singleton instance */
    protected static $_instances = array();

    /**
     * Returns an instance of this class for a preset
     * (this class uses the singleton pattern)
     *
     * @param $preset string Marketplace to use
     * @return Maestrano_Sso_Service
     */
    public static function instanceWithPreset($preset) {
        if (!array_key_exists($preset, self::$_instances) || is_null(self::$_instances[$preset])) {
            self::$_instances[$preset] = new self($preset);
        }
        return self::$_instances[$preset];
    }

    /**
     * Maestrano_Sso_Service constructor.
     * @param $preset string
     */
    public function __construct($preset)
    {
        $this->_preset = $preset;
    }

    /**
     * Return the path used to initiate SSO request
     *
     * @return string Init path
     */
    public function getInitPath() {
        return Maestrano::with($this->_preset)->param('sso.init_path');
    }

    /**
     * Return where the app should redirect internally to initiate SSO request
     *
     * @return string Init Url
     */
    public function getInitUrl() {
        $host = Maestrano::with($this->_preset)->param('app.host');
        $path = $this->getInitPath();
        return "${host}${path}";
    }

    /**
     * The path where the SSO response will be posted and consumed.
     *
     * @return string
     */
    public function getConsumePath() {
        return Maestrano::with($this->_preset)->param('sso.consume_path');
    }

    /**
     * The URL where the SSO response will be posted and consumed.
     *
     * @return string
     */
    public function getConsumeUrl() {
        $host = Maestrano::with($this->_preset)->param('app.host');
        $path = $this->getConsumePath();
        return "${host}${path}";
    }

    /**
     * The URL the user should be redirected after app logged user out
     *
     * @param $userUid User UID to logout
     * @return string url
     */
    public function getLogoutUrl($userUid) {
        $host = Maestrano::with($this->_preset)->param('sso.idp');
        $endpoint = '/app_logout';

        return "${host}${endpoint}?user_uid=${userUid}";
    }

    /**
     * Return the host of the marketplace
     *
     * @return string url
     */
    public function getHost() {
        return Maestrano::with($this->_preset)->param('api.host');
    }

    /**
     * Maestrano Single Sign-On processing URL
     *
     * @return string url
     */
    public function getIdpUrl() {
        $host = Maestrano::with($this->_preset)->param('sso.idp');
        $api_base = Maestrano::with($this->_preset)->param('api.base');
        $endpoint = 'auth/saml';
        return "${host}${api_base}${endpoint}";
    }

    /**
     * The Maestrano endpoint in charge of providing session information
     *
     * @param $user_id string Current user id
     * @param $sso_session string SSO session
     * @return string url
     */
    public function getSessionCheckUrl($user_id, $sso_session)  {
        $host = Maestrano::with($this->_preset)->param('sso.idp');
        $api_base = Maestrano::with($this->_preset)->param('api.base');
        $endpoint = 'auth/saml';

        return "${host}${api_base}${endpoint}/${user_id}?session=${sso_session}";
    }

    /**
     * Return a settings object for php-saml
     *
     * @return Maestrano_Saml_Settings SAML settings
     */
    public function getSamlSettings() {
        $settings = new Maestrano_Saml_Settings();

        // Configure SAML
        $settings->idpPublicCertificate = Maestrano::with($this->_preset)->param('sso.x509_certificate');
        $settings->spIssuer = Maestrano::with($this->_preset)->param('api.id');
        // TODO: default value?
        // $settings->requestedNameIdFormat = Maestrano::with($this->_preset)->param('sso.name_id_format');
        $settings->idpSingleSignOnUrl = $this->getIdpUrl();
        $settings->spReturnUrl = $this->getConsumeUrl();

        return $settings;
    }
}
