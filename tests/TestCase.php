<?php

namespace SubscriptionBase;

/**
 * Base class for SubscriptionBase test cases, provides some utility methods for creating
 * objects.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    const CLIENT_ID     = 'ZrxShaSYM6M19Mi74sVHukoXwGgmSyqZrBsSI9UC';
    const CLIENT_SECRET = 'M6NJ7gtbq7iAy5xpiosUgLFk7o3ssQTrEiJ1vIwjgu3dByhdi0qAB10IffEybSbyutg21P5rKW1xapGji5oVakbSXQ5RCMsMeijiS1zihAduE2anEDx4W5iY7HVkV7dS';
    const PLAN_ID       = 'pln_01d8651a67z47s48dkra44eq2d';
    const BILLING_METHOD_ID = 'blm_01d8djvyqmg46aa3ckt1w3gdm6';

    private $mock;

    protected static function authorizeFromEnv()
    {
        $clientId = getenv('SUBSCRIPTIONBASE_CLIENT_ID');
        if (!$clientId) {
            $clientId = self::CLIENT_ID;
        }
        $clientSecret = getenv('SUBSCRIPTIONBASE_CLIENT_SECRET');
        if (!$clientSecret) {
            $clientSecret = self::CLIENT_SECRET;
        }
        SubscriptionBase::setApiKeys($clientId, $clientSecret);
    }

    protected function setUp()
    {
        ApiRequestor::setHttpClient(HttpClient\CurlClient::instance());
        $this->mock = null;
        $this->call = 0;
    }

    protected function mockRequest($method, $path, $params = array(), $return = array('id' => 'myId'))
    {
        $mock = $this->setUpMockRequest();
        $mock->expects($this->at($this->call++))
             ->method('request')
                 ->with(strtolower($method), SubscriptionBase::$apiBase . $path, $this->anything(), $params, false)
                 ->willReturn(array(json_encode($return), 200));
    }

    private function setUpMockRequest()
    {
        if (!$this->mock) {
            self::authorizeFromEnv();
            $this->mock = $this->getMock('\SubscriptionBase\HttpClient\ClientInterface');
            ApiRequestor::setHttpClient($this->mock);
        }
        return $this->mock;
    }

    /**
     * Create a valid test customer.
     */
    protected static function createTestCustomer(array $attributes = array())
    {
        self::authorizeFromEnv();
        if(!$attributes) {
          $attributes = array(
              'udid'  => 'udid-'. self::randomString(),
          );
        }
        return Customer::create($attributes);
    }

    /**
     * Generate a random 8-character string. Useful for ensuring
     * multiple test suite runs don't conflict
     */
    protected static function randomString()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < 10; $i++) {
            $str .= $chars[rand(0, strlen($chars)-1)];
        }

        return $str;
    }

    /**
     * Genereate a semi-random string
     */
    protected static function generateRandomString($length = 24)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTU';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
