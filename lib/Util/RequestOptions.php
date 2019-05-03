<?php

namespace SubscriptionBase\Util;

use SubscriptionBase\Error;

class RequestOptions
{
    public $headers;
    public $access_token;

    public function __construct($accessToken = null, $headers = array())
    {
        $this->access_token  = $accessToken;
        $this->headers = $headers;
    }

    /**
     * Unpacks an options array and merges it into the existing RequestOptions
     * object.
     * @param array|string|null $options a key => value array
     *
     * @return RequestOptions
     */
    public function merge($options)
    {
        $other_options = self::parse($options);
        if ($other_options->access_token === null) {
            $other_options->access_token = $this->access_token;
        }
        $other_options->headers = array_merge($this->headers, $other_options->headers);
        return $other_options;
    }

    /**
     * Unpacks an options array into an RequestOptions object
     * @param array|string|null $options a key => value array
     *
     * @return RequestOptions
     */
    public static function parse($options)
    {
        if ($options instanceof self) {
            return $options;
        }

        if (is_null($options)) {
            return new RequestOptions(null, array());
        }

        if (is_string($options)) {
            return new RequestOptions($options, array());
        }

        if (is_array($options)) {
            $headers = array();
            $key = null;
            if (array_key_exists('access_token', $options)) {
                $key = $options['access_token'];
            }
            if (array_key_exists('idempotency_key', $options)) {
                $headers['Idempotency-Key'] = $options['idempotency_key'];
            }
            if (array_key_exists('subscriptionbase_account', $options)) {
                $headers['SubscriptionBase-Account'] = $options['subscriptionbase_account'];
            }
            if (array_key_exists('subscriptionbase_version', $options)) {
                $headers['SubscriptionBase-Version'] = $options['subscriptionbase_version'];
            }
            if (array_key_exists('locale', $options)) {
                $headers['Locale'] = $options['locale'];
            }
            if (array_key_exists('subscriptionbase_direct_token_generate', $options)) {
                $headers['X-SubscriptionBase-Direct-Token-Generate'] = $options['subscriptionbase_direct_token_generate'];
            }
            return new RequestOptions($key, $headers);
        }

        $message = 'The second argument to SubscriptionBase API method calls is an '
           . 'optional per-request accessToken, which must be a string, or '
           . 'per-request options, which must be an array. (HINT: you can set '
           . 'a global accessToken by "SubscriptionBase::setApiKey(<accessToken>)")';
        throw new Error\Api($message);
    }
}
