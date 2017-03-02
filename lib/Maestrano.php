<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class Maestrano extends Maestrano_Util_PresetObject
{
    // Maestrano PHP API Version
    const VERSION = '1.0.0-RC2';

    /* Internal Config Map */
    protected static $config = array();

    /**
     * Method to fetch config from the dev-platform
     * @param $configFile String: dev-platform configuration file
     */
    public static function autoConfigure($configFile = null) {
        Maestrano_Config_Client::with('dev-platform')->configure($configFile);
        Maestrano_Config_Client::with('dev-platform')->loadMarketplacesConfig();
    }

    /**
     * Check if the pair api_id/api_key is valid for authentication purpose
     * @param $preset string Marketplace to use
     * @param $api_id string API Id
     * @param $api_key string API Key
     * @return bool whether the pair is valid or not
     */
    public static function authenticateWithPreset($preset, $api_id, $api_key) {
        return !is_null($api_id) && !is_null($api_key) &&
        Maestrano::with($preset)->param('api.id') == $api_id && Maestrano::with($preset)->param('api.key') == $api_key;
    }

    /**
     * Return a configuration parameter from a present
     * @param $preset string Marketplace to use
     * @param $parameter string parameter to fetch
     * @return null
     * @throws Maestrano_Config_Error
     */
    public static function paramWithPreset($preset, $parameter) {
        if (empty($preset)) {
            throw new Maestrano_Config_Error('Empty preset name, make sure you are using \'Maestrano::with($marketplace)->someMethod()\'');
        }

        if (!array_key_exists($preset, self::$config)) {
            throw new Maestrano_Config_Error("Maestrano was not configured for preset '$preset'");
        }

        if (array_key_exists($parameter, self::$config[$preset])) {
            return self::$config[$preset][$parameter];
        } else {
            throw new Maestrano_Config_Error("Preset '$preset' does not contain parameter '$parameter'");
        }
    }

    /**
     * Return the SSO service
     *
     * @return Maestrano_Sso_Service singleton
     */
    public static function ssoWithPreset($preset) {
        return Maestrano_Sso_Service::instanceWithPreset($preset);
    }

    /**
     * @return array List of configured marketplaces
     */
    public static function getMarketplacesList() {
        return array_keys(self::$config);
    }

    /**
     * Configure a Maestrano marketplace from an array
     *
     * @param $preset string The marketplace nid to configure
     * @param $settings array Configuration settings
     * @return true
     * @throws Maestrano_Config_Error
     */
    public static function configureWithPreset($preset, $settings) {
        // Load from JSON file if filename provided
        if (is_string($settings) && is_file($settings)) {
            throw new Maestrano_Config_Error("Metadata files are not accepted anymore, please use the Developer Platform");
        }

        // Ensure preset is initialized
        if (!array_key_exists($preset, self::$config) || is_null(self::$config[$preset])) {
            self::$config[$preset] = array();
        }

        //-------------------------------
        // App Config
        //-------------------------------
        if (array_key_exists('nid', $settings)) {
            self::$config[$preset]['nid'] = $settings['nid'];
        }

        if (array_key_exists('marketplace', $settings)) {
            self::$config[$preset]['marketplace'] = $settings['marketplace'];
        }

        if (array_key_exists('environment', $settings)) {
            self::$config[$preset]['environment'] = $settings['environment'];
        }

        if (array_key_exists('app', $settings) && array_key_exists('host', $settings['app'])) {
            self::$config[$preset]['app.host'] = $settings['app']['host'];
        }

        if (array_key_exists('app', $settings) && array_key_exists('synchronization_start_path', $settings['app'])) {
            self::$config[$preset]['app.synchronization_start_path'] = $settings['app']['synchronization_start_path'];
        }

        if (array_key_exists('app', $settings) && array_key_exists('synchronization_toggle_path', $settings['app'])) {
            self::$config[$preset]['app.synchronization_toggle_path'] = $settings['app']['synchronization_toggle_path'];
        }

        if (array_key_exists('app', $settings) && array_key_exists('synchronization_status_path', $settings['app'])) {
            self::$config[$preset]['app.synchronization_status_path'] = $settings['app']['synchronization_status_path'];
        }

        //-------------------------------
        // API Config
        //-------------------------------
        if (array_key_exists('api', $settings) && array_key_exists('id', $settings['api'])) {
            self::$config[$preset]['api.id'] = $settings['api']['id'];
        }

        if (array_key_exists('api', $settings) && array_key_exists('key', $settings['api'])) {
            self::$config[$preset]['api.key'] = $settings['api']['key'];
        }

        if (array_key_exists('api', $settings) && array_key_exists('host', $settings['api'])) {
            self::$config[$preset]['api.host'] = $settings['api']['host'];
        }

        if (array_key_exists('api', $settings) && array_key_exists('base', $settings['api'])) {
            self::$config[$preset]['api.base'] = $settings['api']['base'];
        }

        // Get lang/platform version
        self::$config[$preset]['api.version'] = Maestrano::VERSION;
        self::$config[$preset]['api.lang'] = 'php';
        self::$config[$preset]['api.lang_version'] = phpversion() . " " . php_uname();

        // Build api.token from api.id and api.key
        self::$config[$preset]['api.token'] = self::$config[$preset]['api.id'] . ":" . self::$config[$preset]['api.key'];

        //-------------------------------
        // SSO Config
        //-------------------------------
        if (array_key_exists('sso', $settings) && array_key_exists('idm', $settings['sso'])) {
            self::$config[$preset]['sso.idm'] = $settings['sso']['idm'];
        }

        if (array_key_exists('sso', $settings) && array_key_exists('init_path', $settings['sso'])) {
            self::$config[$preset]['sso.init_path'] = $settings['sso']['init_path'];
        }

        if (array_key_exists('sso', $settings) && array_key_exists('consume_path', $settings['sso'])) {
            self::$config[$preset]['sso.consume_path'] = $settings['sso']['consume_path'];
        }

        if (array_key_exists('sso', $settings) && array_key_exists('idp', $settings['sso'])) {
            self::$config[$preset]['sso.idp'] = $settings['sso']['idp'];
        }

        if (array_key_exists('sso', $settings) && array_key_exists('x509_fingerprint', $settings['sso'])) {
            self::$config[$preset]['sso.x509_fingerprint'] = $settings['sso']['x509_fingerprint'];
        }

        if (array_key_exists('sso', $settings) && array_key_exists('x509_certificate', $settings['sso'])) {
            self::$config[$preset]['sso.x509_certificate'] = $settings['sso']['x509_certificate'];
        }

        //-------------------------------
        // Connec! Config
        //-------------------------------
        if (array_key_exists('connec', $settings) && array_key_exists('host', $settings['connec'])) {
            self::$config[$preset]['connec.host'] = $settings['connec']['host'];
        }

        if (array_key_exists('connec', $settings) && array_key_exists('base_path', $settings['connec'])) {
            self::$config[$preset]['connec.base_path'] = $settings['connec']['base_path'];
        }

        if (array_key_exists('connec', $settings) && array_key_exists('timeout', $settings['connec'])) {
            self::$config[$preset]['connec.timeout'] = $settings['connec']['timeout'];
        }

        //-------------------------------
        // Webhook Config - Account
        //-------------------------------
        if (array_key_exists('webhooks', $settings)
            && array_key_exists('account', $settings['webhooks'])
            && array_key_exists('group_path', $settings['webhooks']['account'])) {
            self::$config[$preset]['webhooks.account.group_path'] = $settings['webhooks']['account']['group_path'];
        }

        if (array_key_exists('webhooks', $settings)
            && array_key_exists('account', $settings['webhooks'])
            && array_key_exists('group_user_path', $settings['webhooks']['account'])) {
            self::$config[$preset]['webhooks.account.group_user_path'] = $settings['webhooks']['account']['group_user_path'];
        }

        //-------------------------------
        // Webhook Config - Connec
        //-------------------------------
        if (array_key_exists('webhooks', $settings)
            && array_key_exists('connec', $settings['webhooks'])
            && array_key_exists('external_ids', $settings['webhooks']['connec'])) {
            self::$config[$preset]['webhooks.connec.external_ids'] = $settings['webhooks']['connec']['external_ids'];
        }

        if (array_key_exists('webhooks', $settings)
            && array_key_exists('connec', $settings['webhooks'])
            && array_key_exists('initialization_path', $settings['webhooks']['connec'])) {
            self::$config[$preset]['webhooks.connec.initialization_path'] = $settings['webhooks']['connec']['initialization_path'];
        }

        if (array_key_exists('webhooks', $settings)
            && array_key_exists('connec', $settings['webhooks'])
            && array_key_exists('notification_path', $settings['webhooks']['connec'])) {
            self::$config[$preset]['webhooks.connec.notification_path'] = $settings['webhooks']['connec']['notification_path'];
        }

        return true;
    }

    /**
     * Return a json string describing the configuration
     * currently used by the PHP bindings
     */
    public static function toMetadataWithPreset($preset) {
        $config = array(
            'nid' => Maestrano::with($preset)->param('nid'),
            'marketplace' => Maestrano::with($preset)->param('marketplace'),
            'environment' => Maestrano::with($preset)->param('environment'),
            'app' => array(
                'host' => Maestrano::with($preset)->param('app.host'),
                'synchronization_start_path' => Maestrano::with($preset)->param('app.synchronization_start_path'),
                'synchronization_toggle_path' => Maestrano::with($preset)->param('app.synchronization_toggle_path'),
                'synchronization_status_path' => Maestrano::with($preset)->param('app.synchronization_status_path')
            ),
            'api' => array(
                'id' => Maestrano::with($preset)->param('api.id'),
                'key' => Maestrano::with($preset)->param('api.key'),
                'host' => Maestrano::with($preset)->param('api.host'),
                'base' => Maestrano::with($preset)->param('api.base'),
                'version' => Maestrano::with($preset)->param('api.version'),
                'lang' => Maestrano::with($preset)->param('api.lang'),
                'lang_version' => Maestrano::with($preset)->param('api.lang_version')
            ),
            'sso' => array(
                'idm' => Maestrano::with($preset)->param('sso.idm'),
                'init_path' => Maestrano::with($preset)->param('sso.init_path'),
                'consume_path' => Maestrano::with($preset)->param('sso.consume_path'),
                'idp' => Maestrano::with($preset)->param('sso.idp'),
            ),
            'connec' => array(
                'host' => Maestrano::with($preset)->param('connec.host'),
                'base_path' => Maestrano::with($preset)->param('connec.base_path'),
                'timeout' => Maestrano::with($preset)->param('connec.timeout')
            ),
            'webhooks' => array(
                'account' => array(
                    'group_path' => Maestrano::with($preset)->param('webhooks.account.group_path'),
                    'group_user_path' => Maestrano::with($preset)->param('webhooks.account.group_user_path')
                ),
                'connec' => array(
                    'external_ids' => Maestrano::with($preset)->param('webhooks.connec.external_ids'),
                    'initialization_path' => Maestrano::with($preset)->param('webhooks.connec.initialization_path'),
                    'notification_path' => Maestrano::with($preset)->param('webhooks.connec.notification_path')
                )
            )
        );

        return json_encode($config);
    }
}
