<?php

namespace SubscriptionBase;

class AuthenticationErrorTest extends TestCase
{
    public function testInvalidCredentials()
    {
        SubscriptionBase::setApiKeys('invalid', 'invalid');
        try {
            Customer::create();
        } catch (Error\Authentication $e) {
            $this->assertSame(401, $e->getHttpStatus());
        }
    }
}
