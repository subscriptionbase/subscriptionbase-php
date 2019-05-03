<?php

namespace SubscriptionBase;

class CustomerSubscription extends ApiResource
{
    /**
     * @return string The instance URL for this resource. It needs to be special
     *    cased because it doesn't fit into the standard resource pattern.
     */
    public function instanceUrl()
    {
        $id = $this['id'];
        if (!$id) {
            $class = get_class($this);
            $msg = "Could not determine which URL to request: $class instance "
             . "has invalid ID: $id";
            throw new Error\InvalidRequest($msg, null);
        }

        if ($this['customer']) {
            $parent = $this['customer'];
            $base = Customer::classUrl();
            $path = 'subscriptions';
        } else {
            return null;
        }

        $parent = Util\Util::utf8($parent);
        $id = Util\Util::utf8($id);

        $parentExtn = urlencode($parent);
        $extn = urlencode($id);
        return $base . $parentExtn . '/' . $path . '/'. $extn . '/';
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of Subscriptions.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

}
