<?php

/**
 * Maestrano Connec! HTTP Client.
 */
class Maestrano_Connec_Client extends Maestrano_Util_PresetObject
{
    private $group_id;

    /**
     * Constructor
     * @param group_id The customer group id (defaults to Maestrano configuration)
     */
    private function __construct($group_id = null)
    {
        $this->group_id = $group_id;
    }

    /**
     * @param string $preset Marketplace to use
     * @param string $group_id Group Id in session
     * @return Maestrano_Connec_Client
     */
    public static function newWithPreset($preset, $group_id = null)
    {
        $obj = new Maestrano_Connec_Client($group_id);
        $obj->_preset = $preset;
        return $obj;
    }

    public function getBaseHost()
    {
        return Maestrano::with($this->_preset)->param('connec.host');
    }

    public function getBaseUrl()
    {
        return Maestrano::with($this->_preset)->param('connec.host') . Maestrano::with($this->_preset)->param('connec.base_path');
    }

    public function getReportsUrl()
    {
        return Maestrano::with($this->_preset)->param('connec.host') . "/api/reports";
    }

    /**
     * Perform a GET request to Connec!
     *
     * @param $relativePath string The relative path to the resource or resource collection
     * @param $params string Optional filtering parameters
     * @return array Array containing the response. E.g. ( 'code' => 200, 'body' => {...} )
     */
    public function get($relativePath, $params = null)
    {
        return $this->_curlRequest(
            'GET',
            $this->scopedUrl($this->getBaseUrl(), $relativePath),
            $this->defaultHeaders(),
            $params
        );
    }

    /**
     * Perform a GET request to Connec! reports
     *
     * @param $relativePath string The relative path to the report
     * @param $params string Optional filtering parameters
     * @return array Array containing the response. E.g. ( 'code' => 200, 'body' => {...} )
     */
    public function getReport($relativePath, $params = null)
    {
        return $this->_curlRequest(
            'GET',
            $this->scopedUrl($this->getReportsUrl(), $relativePath),
            $this->defaultHeaders(),
            $params
        );
    }

    /**
     * Perform a POST request to Connec!
     *
     * @param $relativePath string The relative path to the resource or resource collection
     * @param $attributes array Associative array of attributes
     * @return array Array containing the response. E.g. ( 'code' => 200, 'body' => {...} )
     */
    public function post($relativePath, $attributes = null)
    {
        return $this->_curlRequest(
            'POST',
            $this->scopedUrl($this->getBaseUrl(), $relativePath),
            $this->defaultHeaders(),
            $attributes
        );
    }

    /**
     * Perform a PUT request to Connec!
     *
     * @param $relativePath string The relative path to the resource or resource collection
     * @param $attributes array Associative array of attributes
     * @return array Array containing the response. E.g. ( 'code' => 200, 'body' => {...} )
     */
    public function put($relativePath, $attributes = null)
    {
        return $this->_curlRequest(
            'PUT',
            $this->scopedUrl($this->getBaseUrl(), $relativePath),
            $this->defaultHeaders(),
            $attributes
        );
    }

    /**
     * @return array The default HTTP headers
     */
    private function defaultHeaders()
    {
        $apiToken = Maestrano::param('api.token');

        return array(
            'Authorization: Basic ' . base64_encode($apiToken),
            'Accept: application/vnd.api+json',
            'Content-Type: application/vnd.api+json',
            'Connec-Country-Format: alpha2'
        );
    }

    /**
     * @param $api string The API to use (eg. v2 or reports)
     * @param $path string The $relativePath API resource path. E.g. '/organizations'
     * @return string the absolute url to the resource
     */
    private function scopedUrl($api, $path)
    {
        return $api . $this->scopedPath($path);
    }

    /**
     * @param $relativePath string The API resource path. E.g. '/organizations'
     * @return string The relative path prefixed with the group_id
     */
    private function scopedPath($relativePath)
    {
        $clean_path = preg_replace('/^\/+/', '', $relativePath);
        $clean_path = preg_replace('/\/+$/', '', $clean_path);

        return "/" . $this->group_id . "/" . $clean_path;
    }

    /**
     * @param array $arr An map of param keys to values.
     *
     * @return string A querystring, essentially.
     */
    public static function encode($arr)
    {
        if (!is_array($arr))
            return $arr;

        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v))
                continue;

            if (is_array($v)) {
                $r[] = self::encode($v, $k, true);
            } else {
                $r[] = urlencode($k) . "=" . urlencode($v);
            }
        }

        return implode("&", $r);
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if it wasn't a string.
     */
    public static function utf8($value)
    {
        if (is_string($value)
            && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8"
        ) {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    private function _curlRequest($method, $absUrl, $headers, $params)
    {
        $curl = curl_init();
        $method = strtoupper($method);
        $opts = array();
        if ($method == 'GET') {
            $opts[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else if ($method == 'POST') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);

        } else if ($method == 'PUT') {
            $opts[CURLOPT_CUSTOMREQUEST] = "PUT";
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);

        } else if ($method == 'DELETE') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else {
            throw new Maestrano_Api_Error("Unrecognized method $method");
        }

        $absUrl = self::utf8($absUrl);
        $opts[CURLOPT_URL] = $absUrl;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        $opts[CURLOPT_TIMEOUT] = 80;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;

        curl_setopt_array($curl, $opts);
        $rbody = curl_exec($curl);

        if (!defined('CURLE_SSL_CACERT_BADFILE')) {
            define('CURLE_SSL_CACERT_BADFILE', 77);  // constant not defined in PHP
        }

        $errno = curl_errno($curl);
        if ($errno == CURLE_SSL_CACERT ||
            $errno == CURLE_SSL_PEER_CERTIFICATE ||
            $errno == CURLE_SSL_CACERT_BADFILE
        ) {
            array_push(
                $headers,
                'X-Maestrano-Client-Info: {"ca":"using Maestrano-supplied CA bundle"}'
            );
            $cert = $this->caBundle();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CAINFO, $cert);
            $rbody = curl_exec($curl);
        }

        if ($rbody === false) {
            $errno = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($errno, $message);
        }

        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return array('body' => $rbody, 'code' => $rcode);
    }

    private function handleCurlError($errno, $message)
    {
        throw new Maestrano_Api_Error("curl_errno: $errno, message: $message");
    }
}
