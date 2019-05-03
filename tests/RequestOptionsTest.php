<?php

namespace SubscriptionBase;
use SubscriptionBase\Util\Set;
use SubscriptionBase\Customer;

class RequestOptionsTest extends TestCase
{
    public function testStringAPIKey()
    {
        $opts = Util\RequestOptions::parse("foo");
        $this->assertSame("foo", $opts->access_token);
        $this->assertSame(array(), $opts->headers);
    }

    public function testNull()
    {
        $opts = Util\RequestOptions::parse(null);
        $this->assertSame(null, $opts->access_token);
        $this->assertSame(array(), $opts->headers);
    }

    public function testEmptyArray()
    {
        $opts = Util\RequestOptions::parse(array());
        $this->assertSame(null, $opts->access_token);
        $this->assertSame(array(), $opts->headers);
    }

    public function testAPIKeyArray()
    {
        $opts = Util\RequestOptions::parse(
            array(
                'access_token' => 'foo',
            )
        );
        $this->assertSame('foo', $opts->access_token);
        $this->assertSame(array(), $opts->headers);
    }

    public function testIdempotentKeyArray()
    {
        $opts = Util\RequestOptions::parse(
            array(
                'idempotency_key' => 'foo',
            )
        );
        $this->assertSame(null, $opts->access_token);
        $this->assertSame(array('Idempotency-Key' => 'foo'), $opts->headers);
    }

    public function testLocaleArray()
    {
        $opts = Util\RequestOptions::parse(
            array(
                'locale' => 'ja',
            )
        );
        $this->assertSame(null, $opts->access_token);
        $this->assertSame(array('Locale' => 'ja'), $opts->headers);
    }

    public function testKeyArray()
    {
        $opts = Util\RequestOptions::parse(
            array(
                'idempotency_key' => 'foo',
                'access_token' => 'foo'
            )
        );
        $this->assertSame('foo', $opts->access_token);
        $this->assertSame(array('Idempotency-Key' => 'foo'), $opts->headers);
    }

    /**
     * @expectedException SubscriptionBase\Error\Api
     */
    public function testWrongType()
    {
        $opts = Util\RequestOptions::parse(5);
    }
}
