<?php

namespace SubscriptionBase\Util;

use SubscriptionBase\SubscriptionBaseObject;

abstract class Util
{
    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }

      // TODO: generally incorrect, but it's correct given SubscriptionBase's response
        foreach (array_keys($array) as $k) {
            if (!is_numeric($k)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively converts the PHP SubscriptionBase object to an array.
     *
     * @param array $values The PHP SubscriptionBase object to convert.
     * @return array
     */
    public static function convertSubscriptionBaseObjectToArray($values)
    {
        $results = array();
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof SubscriptionBaseObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertSubscriptionBaseObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the SubscriptionBase API to the corresponding PHP object.
     *
     * @param array $resp The response from the SubscriptionBase API.
     * @param array $opts
     * @return Object|array
     */
    public static function convertToSubscriptionBaseObject($resp, $opts)
    {
        $types = array(
            'list'           => 'SubscriptionBase\\Collection',
            'customer'       => 'SubscriptionBase\\Customer',
            'subscription'   => 'SubscriptionBase\\Subscription',
            'paymentmethod'  => 'SubscriptionBase\\PaymentMethod',
        );
        if (self::isList($resp)) {
            $mapped = array();
            foreach ($resp as $i) {
                array_push($mapped, self::convertToSubscriptionBaseObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object_name']) && is_string($resp['object_name']) && isset($types[$resp['object_name']])) {
                $class = $types[$resp['object_name']];
            } else {
                $class = 'SubscriptionBase\\SubscriptionBaseObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (is_string($value) && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }
}
