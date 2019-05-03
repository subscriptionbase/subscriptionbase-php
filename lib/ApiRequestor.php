<?php

namespace SubscriptionBase;

class ApiRequestor
{
    private $_clientId;
    private $_clientSecret;
    private $_accessToken;
    private $_apiBase;
    private static $_httpClient;

    public function __construct($accessToken = null, $apiBase = null)
    {
        $this->_clientId     = SubscriptionBase::$clientId;
        $this->_clientSecret = SubscriptionBase::$clientSecret;
        $this->_accessToken  = $accessToken;
        $this->_apiBase      = $apiBase ? $apiBase : SubscriptionBase::$apiBase;
    }

    public function _accessTokenRequest($clientId = null, $clientSecret = null)
    {
      if (!$clientId) {
          $msg = 'No clientId provided.  (HINT: set your API key using '
            . '"SubscriptionBase::setClientId(<ClientId>)".  You can generate API keys from '
            . 'the SubscriptionBase web interface.';
          throw new Error\Authentication($msg);
      }
      if (!$clientSecret) {
          $msg = 'No clientSecret provided.  (HINT: set your API key using '
            . '"SubscriptionBase::setClientSecret(<ClientSecret>)".  You can generate API keys from '
            . 'the SubscriptionBase web interface.';
          throw new Error\Authentication($msg);
      }

      $params = self::_encodeObjects(array(
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'grant_type'    => 'client_credentials',
        'scope'         => 'tenant_application'
      ));
      $langVersion = phpversion();
      $uname = php_uname();
      $ua = array(
          'bindings_version' => SubscriptionBase::VERSION,
          'lang'             => 'php',
          'lang_version'     => $langVersion,
          'publisher'        => 'subscriptionbase',
          'uname'            => $uname,
      );
      $defaultHeaders = array(
          'X-SubscriptionBase-Client-User-Agent' => json_encode($ua),
          'User-Agent'   => 'SubscriptionBase/v1 PhpBindings/' . SubscriptionBase::VERSION,
          'Content-Type' => 'application/x-www-form-urlencoded',
      );
      if (SubscriptionBase::$apiVersion) {
          $defaultHeaders['SubscriptionBase-Version'] = SubscriptionBase::$apiVersion;
      }
      $rawHeaders = array();
      foreach ($defaultHeaders as $header => $value) {
          $rawHeaders[] = $header . ': ' . $value;
      }

      list($rbody, $rcode) = $this->httpClient()->request(
          'post',
          SubscriptionBase::$apiBase . '/oauth2/token/',
          $rawHeaders,
          $params,
          /* $hasFile=*/ false
      );
      if($rcode !== 200) {
        $this->_interpretResponse($rbody, $rcode);
      }
      $resp = json_decode($rbody, true);

      return $resp['access_token'];
    }

    private static function _encodeObjects($d)
    {
        if ($d instanceof ApiResource) {
            return Util\Util::utf8($d->id);
        } elseif ($d === true) {
            return 'true';
        } elseif ($d === false) {
            return 'false';
        } elseif (is_array($d)) {
            $res = array();
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }
            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $params
     * @param array|null $headers
     *
     * @return array An array whose first element is the response and second
     *    element is the API key used to make the request.
     */
    public function request($method, $url, $params = null, $headers = null)
    {
        if (!$params) {
            $params = array();
        }
        if (!$headers) {
            $headers = array();
        }
        list($rbody, $rcode, $accessToken) =
          $this->_requestRaw($method, $url, $params, $headers);
        $resp = $this->_interpretResponse($rbody, $rcode);
        return array($resp, $accessToken);
    }

    /**
     * @param string $rbody A JSON string.
     * @param int $rcode
     * @param array $resp
     *
     * @throws Error\InvalidRequest if the error is caused by the user.
     * @throws Error\Authentication if the error is caused by a lack of permissions.
     * @throws Error\Api otherwise.
     */
    public function handleApiError($rbody, $rcode, $resp)
    {
        if (!is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody, $resp);
        }

        $error = $resp['error'];
        $code = isset($error['error_code']) ? $error['error_code'] : null;
        $msg  = isset($error['message']) ? $error['message'] : null;
        $msg  = is_array(json_encode($msg));

        switch ($rcode) {
            case 400:
            case 404:
                throw new Error\InvalidRequest($msg, $rcode, $rbody, $resp);
            case 401:
                throw new Error\Authentication($msg, $rcode, $rbody, $resp);
            default:
                throw new Error\Api($msg, $rcode, $rbody, $resp);
        }
    }

    private function _requestRaw($method, $url, $params, $headers)
    {
        // TODO : 有効期間判定を追加
        if (!$this->_accessToken) {
            $this->_accessToken = self::_accessTokenRequest($this->_clientId, $this->_clientSecret);
        }

        if (!$this->_accessToken) {
            $msg = 'No API key provided.  (HINT: set your API key using '
              . '"SubscriptionBase::setApiKey(<API-KEY>)".  You can generate API keys from '
              . 'the SubscriptionBase web interface.';
            throw new Error\Authentication($msg);
        }

        $absUrl = $this->_apiBase . $url;
        $params = self::_encodeObjects($params);
        $langVersion = phpversion();
        $uname = php_uname();
        $ua = array(
            'bindings_version' => SubscriptionBase::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'subscriptionbase',
            'uname' => $uname,
        );
        $defaultHeaders = array(
            'X-SubscriptionBase-Client-User-Agent' => json_encode($ua),
            'User-Agent' => 'SubscriptionBase/v1 PhpBindings/' . SubscriptionBase::VERSION,
            'Authorization' => 'Bearer ' . $this->_accessToken
        );
        if (SubscriptionBase::$apiVersion) {
            $defaultHeaders['SubscriptionBase-Version'] = SubscriptionBase::$apiVersion;
        }
        $hasFile = false;
        $hasCurlFile = class_exists('\CURLFile', false);
        foreach ($params as $k => $v) {
            if (is_resource($v)) {
                $hasFile = true;
                $params[$k] = self::_processResourceParam($v, $hasCurlFile);
            } elseif ($hasCurlFile && $v instanceof \CURLFile) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $defaultHeaders['Content-Type'] = 'multipart/form-data';
        } else {
            $defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);
        $rawHeaders = array();

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }

        list($rbody, $rcode) = $this->httpClient()->request(
            $method,
            $absUrl,
            $rawHeaders,
            $params,
            $hasFile
        );
        return array($rbody, $rcode, $this->_accessToken);
    }

    private function _processResourceParam($resource, $hasCurlFile)
    {
        if (get_resource_type($resource) !== 'stream') {
            throw new Error\Api(
                'Attempted to upload a resource that is not a stream'
            );
        }

        $metaData = stream_get_meta_data($resource);
        if ($metaData['wrapper_type'] !== 'plainfile') {
            throw new Error\Api(
                'Only plainfile resource streams are supported'
            );
        }

        if ($hasCurlFile) {
            // We don't have the filename or mimetype, but the API doesn't care
            return new \CURLFile($metaData['uri']);
        } else {
            return '@'.$metaData['uri'];
        }
    }

    private function _interpretResponse($rbody, $rcode)
    {
        try {
            $resp = json_decode($rbody, true);
        } catch (Exception $e) {
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $resp);
        }
        return $resp;
    }

    public static function setHttpClient($client)
    {
        self::$_httpClient = $client;
    }

    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance();
        }
        return self::$_httpClient;
    }
}
