<?php

namespace SubscriptionBase;

class CustomerTest extends TestCase
{
    // POST/customers
    public function testCreate()
    {
        $customer = self::createTestCustomer();
        $this->assertSame(strlen($customer->id), 30);
    }

    // GET/customers/:id
    public function testRetrieve()
    {
        $customer = self::createTestCustomer();
        $retrieve_customer = Customer::retrieve($customer->id);

        $this->assertSame($customer->id, $retrieve_customer->id);
    }

    // GET/customers/
    public function testAll()
    {
        $customers = Customer::all(
            array(
                'page' => 1,
            )
        );
    }

    // DELETE/customers/:id
    public function testDeletion()
    {
        $customer = self::createTestCustomer();
        $id = $customer->id;

        $delete_customer = $customer->delete();

        $this->assertSame($id, $customer->id);
        $this->assertTrue($customer->archive_at !== null);

        $this->assertSame($id, $delete_customer->id);
        $this->assertTrue($delete_customer->archive_at !== null);
    }


}
