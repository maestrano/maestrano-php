<?php

class Maestrano_Config_Client extends Maestrano_Util_PresetObject
{
    /* Internal Config Map */
    protected static $config = array();

    /**
     * @param $preset
     * @param $settings
     * @return array
     * @throws Maestrano_Config_Error
     */
    public static function configureWithPreset($preset, $settings) {
        // Load from JSON file if string provided
        if (is_string($settings)) {
            return self::configureWithPreset($preset, json_decode(file_get_contents($settings), true));
        }

        // Ensure preset is initialized
        if (!array_key_exists($preset, self::$config) || is_null(self::$config[$preset])) {
            self::$config[$preset] = array();
        }
        
        //-------------------------------
        // Dev Platform Config
        //-------------------------------
        if (array_key_exists('dev-platform', $settings) && array_key_exists('host', $settings['dev-platform'])) {
            self::$config[$preset]['dev-platform.host'] = $settings['dev-platform']['host'];
        } else {
            self::throwMissingParameterError('dev-platform.host', $settings);
        }

        if (array_key_exists('dev-platform', $settings) && array_key_exists('v1_path', $settings['dev-platform'])) {
            self::$config[$preset]['dev-platform.v1_path'] = $settings['dev-platform']['v1_path'];
        } else {
            self::throwMissingParameterError('dev-platform.v1_path', $settings);
        }

        if (array_key_exists('environment', $settings) && array_key_exists('name', $settings['environment'])) {
            self::$config[$preset]['environment.name'] = $settings['environment']['name'];
        } else {
            self::throwMissingParameterError('environment.name', $settings);
        }

        if (array_key_exists('environment', $settings) && array_key_exists('api_key', $settings['environment'])) {
            self::$config[$preset]['environment.api_key'] = $settings['environment']['api_key'];
        } else {
            self::throwMissingParameterError('environment.api_key', $settings);
        }

        if (array_key_exists('environment', $settings) && array_key_exists('api_secret', $settings['environment'])) {
            self::$config[$preset]['environment.api_secret'] = $settings['environment']['api_secret'];
        } else {
            self::throwMissingParameterError('environment.api_secret', $settings);
        }

        return self::$config[$preset];
    }

    /**
     * Fetch the dynamic endpoints configuration
     *
     * @return Maestrano_Config_Client
     */
    public static function loadMarketplacesConfigWithPreset($preset) {
        $apiKey = self::$config[$preset]['environment.api_key'];
        $apiSecret = self::$config[$preset]['environment.api_secret'];
        $host = self::$config[$preset]['dev-platform.host'];
        $v1_path = self::$config[$preset]['dev-platform.v1_path'];

        // Call to the dev-platform
        $response = \Httpful\Request::get($host.$v1_path)
            ->authenticateWith($apiKey, $apiSecret)
            ->send();

        // Httpful is dumb and doesn't allow you to get json as an associative array but only as an object
        $json_body = json_decode($response->raw_body, true);

        self::with($preset)->loadMultipleMarketplaces($json_body['marketplaces']);
    }

    /**
     * @param $conf_array array Array containing the environments to load
     */
    public static function loadMultipleMarketplacesWithPreset($preset, $conf_array)
    {
        // Load every environments
        foreach ($conf_array as $marketplace) {
            Maestrano::with($marketplace['marketplace'])->configure($marketplace);
        }
    }

    /**
     * @param $parameter
     * @param $file
     * @throws Maestrano_Config_Error
     */
    public static function throwMissingParameterError($parameter, $file) {
        throw new Maestrano_Config_Error("Missing '$parameter' parameter in dev-platform config file.");
    }
}