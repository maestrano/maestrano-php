<?php

class Maestrano_Config_Client extends Maestrano_Util_PresetObject
{
    /* Internal Config Map */
    protected static $config = array();
    protected static $cache = NULL;

    /**
     * Configure the Maestrano PHP SDK using the Developer Platform
     *
     * @param $preset string Dev-Platform configuration preset
     * @param $settings string Configuration file path (optional)
     * @return array Parsed configuration
     * @throws Maestrano_Config_Error
     */
    public static function configureWithPreset($preset, $settings = null)
    {
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
        self::configureDevPlatformSetting('dev-platform', 'host', 'MNO_DEVPL_HOST', $preset, $settings);
        self::configureDevPlatformSetting('dev-platform', 'api_path', 'MNO_DEVPL_API_PATH', $preset, $settings);
        self::configureDevPlatformSetting('environment', 'api_key', 'MNO_DEVPL_ENV_KEY', $preset, $settings);
        self::configureDevPlatformSetting('environment', 'api_secret', 'MNO_DEVPL_ENV_SECRET', $preset, $settings);

        return self::$config[$preset];
    }

    /**
     * Fetch the dynamic marketplaces configuration for this environment
     * and configure the Maestrano presets
     *
     * @param $preset string Dev-Platform configuration preset
     * @throws Maestrano_Config_Error
     */
    public static function loadMarketplacesConfigWithPreset($preset)
    {
        if (self::$cache == NULL)
            self::$cache = new Maestrano_Util_Cache();
        self::$cache->cache_path = dirname(__FILE__) . '/';
        self::$cache->cache_time = 43200; // 12h

        if(!$data = self::$cache->get_cache('dev-platform')) {
            $apiKey = self::$config[$preset]['environment.api_key'];
            $apiSecret = self::$config[$preset]['environment.api_secret'];
            $host = self::$config[$preset]['dev-platform.host'];
            $api_path = self::$config[$preset]['dev-platform.api_path'];

            try {
                // Call to the dev-platform
                $response = \Httpful\Request::get($host . $api_path . "marketplaces")
                    ->authenticateWith($apiKey, $apiSecret)
                    ->send();
                $data = $response->raw_body;

                if ($response->code >= 400) {
                    throw new Maestrano_Config_Error("HTTP Error, code: $response->code", $response->code);
                }
            } catch (Exception $e) {
                error_log("maestrano-php: An error occurred while retrieving the marketplaces. Error: $e");
                error_log("maestrano-php: Re-using existing cached configuration, if any.");
                if (!$data = self::$cache->get_cached_file('dev-platform'))
                    throw new Maestrano_Config_Error("No cached configuration found.");
            }

            self::$cache->set_cache('dev-platform', $data);
        }

        // Transform json as an associative array
        $json_body = json_decode($data, true);

        // Dev-platform error management
        if (array_key_exists('error', $json_body))
            throw new Maestrano_Config_Error("An error occurred while retrieving the marketplaces. Body content: " . print_r($json_body, true));

        self::loadMultipleMarketplaces($json_body['marketplaces']);
    }

    /**
     * Configure Maestrano presets with fetched marketplaces
     *
     * @param $conf_array array Array containing the environments to load
     */
    public static function loadMultipleMarketplaces($conf_array)
    {
        // Load every environments
        foreach ($conf_array as $marketplace) {
            Maestrano::with($marketplace['marketplace'])->configure($marketplace);
        }
    }

    /**
     * Configure a dev platform setting in the dev platform settings preset
     *
     * @param $setting_bloc string Setting bloc name
     * @param $var_name string Variable name
     * @param $env_var string Environment variable name
     * @param $preset string Dev-Platform configuration preset
     * @param $settings array Configuration file content
     */
    private static function configureDevPlatformSetting($setting_bloc, $var_name, $env_var, $preset, $settings)
    {
        if ($settings != null && array_key_exists($setting_bloc, $settings) && array_key_exists($var_name, $settings[$setting_bloc])) {
            self::$config[$preset]["$setting_bloc.$var_name"] = $settings[$setting_bloc][$var_name];
        } elseif ($host = getenv($env_var)) {
            self::$config[$preset]["$setting_bloc.$var_name"] = $host;
        } else {
            self::throwMissingParameterError("$setting_bloc.$var_name");
        }
    }

    /**
     * Throw a missing parameter error
     *
     * @param $parameter string Name of the missing parameter
     * @throws Maestrano_Config_Error
     */
    public static function throwMissingParameterError($parameter)
    {
        throw new Maestrano_Config_Error("Missing '$parameter' parameter in dev-platform config.");
    }
}