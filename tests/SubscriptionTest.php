<?php

namespace SubscriptionBase;

class SubscriptionTest extends TestCase
{

    public function testSubscriptionMethods()
    {
        $customer = self::createTestCustomer();

        # create
        $sub = Subscription::create(
            array(
                'customer' => $customer->id,
                'plan' => parent::PLAN_ID
            )
        );
        $this->assertSame($sub->plan->id, parent::PLAN_ID);

        # retrieve
        $subscription = Subscription::retrieve($sub->id);
        $this->assertSame($subscription->state, 'active');

        # cancel
        $subscription->cancel();
        $this->assertSame($subscription->state, 'cancel_reserved');
        $sub_cancel = Subscription::retrieve($sub->id);
        $this->assertSame($sub_cancel->state, 'cancel_reserved');

        # destory
        $subscription->delete();
        $this->assertSame($subscription->state, 'immediate_canceled');
        $this->assertTrue(($subscription->churn_at != null));
    }

    public function testAll()
    {
        $customer = self::createTestCustomer();

        $sub = Subscription::create(
            array(
                'customer' => $customer->id,
                'plan' => parent::PLAN_ID
            )
        );

        $sub_2 = Subscription::create(
            array(
                'customer' => $customer->id,
                'plan' => parent::PLAN_ID
            )
        );

        $subs = Subscription::all();
        $this->assertTrue(count($subs->data) > 0);

        $subs = $customer->subscriptions->all();
        $this->assertTrue(count($subs->data) == 2);
    }

}
