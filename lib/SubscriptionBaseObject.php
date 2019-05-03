<?php

namespace SubscriptionBase;

use ArrayAccess;
use InvalidArgumentException;

class SubscriptionBaseObject implements ArrayAccess
{
    /**
     * @var Util\Set Attributes that should not be sent to the API because
     *    they're not updatable (e.g. API key, ID).
     */
    public static $permanentAttributes;
    /**
     * @var Util\Set Attributes that are nested but still updatable from
     *    the parent class's URL (e.g. metadata).
     */
    public static $nestedUpdatableAttributes;

    public static $nestedRelationReferences;

    public static function init()
    {
        self::$permanentAttributes = new Util\Set(array('_opts', 'id'));
        self::$nestedUpdatableAttributes = new Util\Set(array());
        self::$nestedRelationReferences = array(
          'customer' => ['payment_methods', 'subscriptions']
        );
    }

    protected $_opts;
    protected $_values;
    protected $_unsavedValues;
    protected $_transientValues;
    protected $_retrieveOptions;

    public function __construct($id = null, $opts = null)
    {
        $this->_opts = $opts ? $opts : new Util\RequestOptions();
        $this->_values = array();
        $this->_unsavedValues = new Util\Set();
        $this->_transientValues = new Util\Set();

        $this->_retrieveOptions = array();
        if (is_array($id)) {
            foreach ($id as $key => $value) {
                if ($key != 'id') {
                    $this->_retrieveOptions[$key] = $value;
                }
            }
            $id = $id['id'];
        }

        if ($id !== null) {
            $this->id = $id;
        }
    }

    // Standard accessor magic methods
    public function __set($k, $v)
    {
        if ($v === "") {
            throw new InvalidArgumentException(
                'You cannot set \''.$k.'\'to an empty string. '
                .'We interpret empty strings as NULL in requests. '
                .'You may set obj->'.$k.' = NULL to delete the property'
            );
        }

        if (self::$nestedUpdatableAttributes->includes($k)
                && isset($this->$k) && is_array($v)) {
            $this->$k->replaceWith($v);
        } else {
            // TODO: may want to clear from $_transientValues (Won't be user-visible).
            $this->_values[$k] = $v;
        }
        if (!self::$permanentAttributes->includes($k)) {
            $this->_unsavedValues->add($k);
        }
    }

    public function __isset($k)
    {
        return isset($this->_values[$k]);
    }
    public function __unset($k)
    {
        unset($this->_values[$k]);
        $this->_transientValues->add($k);
        $this->_unsavedValues->discard($k);
    }
    public function &__get($k)
    {
        if (array_key_exists($k, $this->_values)) {
            return $this->_values[$k];
        } else if ($this->_transientValues->includes($k)) {
            $class = get_class($this);
            $attrs = join(', ', array_keys($this->_values));
            $message = "SubscriptionBase Notice: Undefined property of $class instance: $k. "
                    . "HINT: The $k attribute was set in the past, however. "
                    . "It was then wiped when refreshing the object "
                    . "with the result returned by SubscriptionBase's API, "
                    . "probably as a result of a save(). The attributes currently "
                    . "available on this object are: $attrs";
            error_log($message);
            return null;
        } else {
            $class = get_class($this);
            error_log("SubscriptionBase Notice: Undefined property of $class instance: $k");
            return null;
        }
    }

    // ArrayAccess methods
    public function offsetSet($k, $v)
    {
        $this->$k = $v;
    }

    public function offsetExists($k)
    {
        return array_key_exists($k, $this->_values);
    }

    public function offsetUnset($k)
    {
        unset($this->$k);
    }
    public function offsetGet($k)
    {
        return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
    }

    public function keys()
    {
        return array_keys($this->_values);
    }

    /**
     * This unfortunately needs to be public to be used in Util\Util
     *
     * @param array $values
     * @param array $opts
     *
     * @return Object The object constructed from the given values.
     */
    public static function constructFrom($values, $opts)
    {
        $obj = new static(isset($values['id']) ? $values['id'] : null);
        $obj->refreshFrom($values, $opts);
        return $obj;
    }

    /**
     * Refreshes this object using the provided values.
     *
     * @param array $values
     * @param array $opts
     * @param boolean $partial Defaults to false.
     */
    public function refreshFrom($values, $opts, $partial = false)
    {
        $this->_opts = $opts;

        if ($partial) {
            $removed = new Util\Set();
        } else {
            $removed = array_diff(array_keys($this->_values), array_keys($values));
        }

        foreach ($removed as $k) {
            if (self::$permanentAttributes->includes($k)) {
                continue;
            }

            unset($this->$k);
        }

        foreach ($values as $k => $v) {
            if (self::$permanentAttributes->includes($k) && isset($this[$k])) {
                continue;
            }

            if (self::$nestedUpdatableAttributes->includes($k) && is_array($v)) {
                $this->_values[$k] = AttachedObject::constructFrom($v, $opts);
            } else {
                $this->_values[$k] = Util\Util::convertToSubscriptionBaseObject($v, $opts);
            }

            $this->_transientValues->discard($k);
            $this->_unsavedValues->discard($k);
        }
        // setup Collection for nested relation attributes.
        if(isset($values['object_name'])) {
          $className = $values['object_name'];
          $nestedRelationAttributes = isset(self::$nestedRelationReferences[$className]) ? self::$nestedRelationReferences[$className] : array();
          foreach($nestedRelationAttributes as $relationAttr) {
            $relationUri = str_replace('_', '-', $relationAttr);
            $value = array(
              'object_name' => 'list',
              'url'         => static::classUrl(). $this['id']. '/' . $relationUri . '/',
            );
            $this->_values[$relationAttr] = Util\Util::convertToSubscriptionBaseObject($value, $opts);
          }
        }
    }

    /**
     * @return array A recursive mapping of attributes to values for this object,
     *    including the proper value for deleted attributes.
     */
    public function serializeParameters()
    {
        $params = array();
        if ($this->_unsavedValues) {
            foreach ($this->_unsavedValues->toArray() as $k) {
                $v = $this->$k;
                if ($v === null) {
                    $v = '';
                }

                $params[$k] = $v;
            }
        }

        // Get nested updates.
        foreach (self::$nestedUpdatableAttributes->toArray() as $property) {
            if (isset($this->$property)) {
                if ($this->$property instanceof Object) {
                    $serialized = $this->$property->serializeParameters();
                    if ($serialized) {
                        $params[$property] = $serialized;
                    }
                }
            }
        }

        return $params;
    }

    public function __toJSON()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
        } else {
            return json_encode($this->__toArray(true));
        }
    }

    public function __toString()
    {
        $class = get_class($this);
        return $class . ' JSON: ' . $this->__toJSON();
    }

    public function __toArray($recursive = false)
    {
        if ($recursive) {
            return Util\Util::convertSubscriptionBaseObjectToArray($this->_values);
        } else {
            return $this->_values;
        }
    }
}

SubscriptionBaseObject::init();
