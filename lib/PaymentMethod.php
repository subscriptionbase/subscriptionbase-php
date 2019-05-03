<?php
namespace SubscriptionBase;

class PaymentMethod extends ApiResource
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
            $path = 'payment-methods';
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
     * @param array|string|null $opts
     *
     * @return array An array of Customers.
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return PaymentMethod The created customer.
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return PaymentMethod The deleted external account.
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    /**
     * @param array|string|null $opts
     *
     * @return PaymentMethod The saved external account.
     */
    /*
    public function save($opts = null)
    {
        return $this->_save($opts);
    }
    */
    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return PaymentMethod The Canceled subscription.
     */
    public function primary($params = null, $options = null)
    {
        $url = $this->instanceUrl() . 'primary/';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
