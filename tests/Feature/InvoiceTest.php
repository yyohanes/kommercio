<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Store;

class InvoiceTest extends \TestCase
{
    protected $order;
    protected $store;

    protected function setUp()
    {
        parent::setUp();

        // Setup order to be tested
        $this->store = factory(Store::class)->create();
        $this->order = factory(Order::class)->create([
            'store_id' => $this->store->id,
            'status' => Order::STATUS_PENDING
        ]);
    }

    public function testInvoiceOverdue()
    {
        $order = $this->order;

        Invoice::createInvoice($order, null, 100, Carbon::now());
        $invoice = Invoice::createInvoice($order, null, 100, Carbon::now());
        $this->assertFalse($invoice->isOverdue());
        $this->assertEquals(0, $invoice->daysToOverdue());

        $qb = Invoice::whereDaysToOverdue(0);
        $this->assertEquals(2, $qb->count());


        Invoice::createInvoice($order, null, 100, Carbon::yesterday());
        $invoice = Invoice::createInvoice($order, null, 100, Carbon::yesterday());
        $this->assertTrue($invoice->isOverdue());
        $this->assertEquals(-1, $invoice->daysToOverdue());

        $qb = Invoice::whereDaysToOverdue(-1);
        $this->assertEquals(2, $qb->count());


        Invoice::createInvoice($order, null, 100, Carbon::tomorrow());
        $invoice = Invoice::createInvoice($order, null, 100, Carbon::tomorrow());
        $this->assertFalse($invoice->isOverdue());
        $this->assertEquals(1, $invoice->daysToOverdue());
        $this->assertTrue($invoice->isOverdue(Carbon::tomorrow()->modify('+1 day')));
        $this->assertEquals(-1, $invoice->daysToOverdue(Carbon::tomorrow()->modify('+1 day')));

        $qb = Invoice::whereDaysToOverdue(1);
        $this->assertEquals(2, $qb->count());


        $invoice = Invoice::createInvoice($order, null, 100);
        $this->assertFalse($invoice->isOverdue());
        $this->assertEquals(0, $invoice->daysToOverdue());
    }

    protected function tearDown()
    {
        $this->order->forceDelete();
        $this->store->delete();

        parent::tearDown();
    }
}