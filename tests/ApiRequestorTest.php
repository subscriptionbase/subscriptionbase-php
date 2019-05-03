<?php

namespace SubscriptionBase;

class ApiRequestorTest extends TestCase
{
    public function testApiTokenRequest()
    {
        $requestor = new ApiRequestor(self::CLIENT_ID, self::CLIENT_SECRET);
        $response = $requestor->_accessTokenRequest(self::CLIENT_ID, self::CLIENT_SECRET);
        $this->assertSame(strlen($response), 30);
    }

    public function testEncodeObjects()
    {
        $reflector = new \ReflectionClass('SubscriptionBase\\ApiRequestor');
        $method = $reflector->getMethod('_encodeObjects');
        $method->setAccessible(true);

        $a = array('customer' => new Customer('abcd'));
        $enc = $method->invoke(null, $a);
        $this->assertSame($enc, array('customer' => 'abcd'));

        // Preserves UTF-8
        $v = array('customer' => "☃");
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, $v);

        // Encodes latin-1 -> UTF-8
        $v = array('customer' => "\xe9");
        $enc = $method->invoke(null, $v);
        $this->assertSame($enc, array('customer' => "\xc3\xa9"));
    }
}
