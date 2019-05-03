<?php

namespace SubscriptionBase;

class PaymentMethodTest extends TestCase
{

    public function testPaymentMethods()
    {
        $customer = self::createTestCustomer();

        # create
        $paymentMethodData = array(
          'billing_method' => parent::BILLING_METHOD_ID,
          'is_primary'     => True,
          'email_address'  => array(
            'mail_to' => 'email@email.com',
            'mail_cc' => 'email_cc1@email.com,email_cc2@email.com',
            'mail_bcc'=> 'email_bcc1@email.com,email_bcc2@email.com',
            'company' => 'company_name',
            'contact_department' => 'company_department',
            'contact_name'       => 'contactor_name',
          )
        );
        $payment1 = $customer->payment_methods->create($paymentMethodData);
        $this->assertSame($payment1->customer, $customer->id);
        $this->assertTrue($payment1->is_primary);
        $payment2 = $customer->payment_methods->create($paymentMethodData);
        $this->assertTrue($payment2->is_primary);

        # retrieve and change primary.
        $payment1 = $customer->payment_methods->retrieve($payment1->id);
        $this->assertFalse($payment1->is_primary);
        $payment1->primary();
        $this->assertTrue($payment1->is_primary);

        $payment2 = $customer->payment_methods->retrieve($payment2->id);
        $this->assertFalse($payment2->is_primary);
        $payment2->delete();
        $this->assertTrue($payment2->archive_at !== null);

    }

    /*
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

        $this->assertSame(count($subs), 2);
    }
    */
}
