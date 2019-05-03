<?php
namespace SubscriptionBase;


class SubscriptionBase
{
    // @var string The SubscriptionBase API key to be used for requests.
    public static $clientId;
    public static $clientSecret;
    public static $accessToken;

    // @var string The base URL for the SubscriptionBase API.
    public static $apiBase = 'http://172.17.0.1:8888';

    // @var string|null The version of the SubscriptionBase API to use for requests.
    public static $apiVersion = null;

    // @var boolean Defaults to true.
    public static $verifySslCerts = true;

    const VERSION = '1.0.0';

    /**
     * Sets the API key to be used for requests.
     *
     * @param string $apiKey
     */
    public static function setApiKeys($clientId, $clientSecret)
    {
        self::$clientId = $clientId;
        self::$clientSecret = $clientSecret;
    }

    /**
     * @return string The API key used for requests.
     */
    public static function getClientId()
    {
        return self::$clientId;
    }

    /**
     * @return string The API key used for requests.
     */
    public static function getClientSecret()
    {
        return self::$clientSecret;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *    latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return boolean
     */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /**
     * @param boolean $verify
     */
    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }
}
