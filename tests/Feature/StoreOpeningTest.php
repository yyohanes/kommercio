<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Kommercio\Models\Product;
use Kommercio\Models\ProductDetail;
use Kommercio\Models\Store;
use Kommercio\Models\Store\OpeningTime;
use TestCase;

class StoreOpeningTest extends TestCase
{
    protected $store;
    protected $product;

    protected function setUp()
    {
        parent::setUp();

        $this->store = factory(Store::class)->create();

        factory(OpeningTime::class)->create([
            'store_id' => $this->store->id,
            'date_from' => null,
            'date_to' => null,
            'time_from' => null,
            'time_to' => null,
            'monday' => TRUE,
            'friday' => FALSE,
            'sort_order' => 2
        ]);

        factory(OpeningTime::class)->create([
            'store_id' => $this->store->id,
            'date_from' => Carbon::tomorrow()->format('Y-m-d'),
            'date_to' => Carbon::tomorrow()->format('Y-m-d'),
            'sort_order' => 0,
        ]);

        $dayAfterTomorrow = Carbon::tomorrow();
        $dayAfterTomorrow->modify('+1 day');

        factory(OpeningTime::class)->create([
            'store_id' => $this->store->id,
            'date_to' => $dayAfterTomorrow->format('Y-m-d'),
            'time_from' => null,
            'time_to' => null,
            'sort_order' => 1,
            'open' => FALSE
        ]);

        $this->product = factory(Product::class)->create();
        factory(ProductDetail::class)->create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id
        ]);
    }

    protected function tearDown()
    {
        $this->store->delete();
        $this->product->forceDelete();

        parent::tearDown();
    }

    /**
     * Test if opening time is correct
     */
    public function testOpeningTime()
    {
        $store = $this->store;

        // Check 1st opening time
        $openingTime = $store->openingTimes->get(0);

        $time = Carbon::tomorrow();
        $time->setTimeFromTimeString('18:00:00');
        $this->assertFalse($openingTime->isOpen($time));

        $time->setTimeFromTimeString('13:00:00');
        $this->assertTrue($openingTime->isOpen($time));

        // Check 2nd opening time
        $openingTime = $store->openingTimes->get(1);

        $time = Carbon::tomorrow();
        $time->format('+1 day');
        $time->setTimeFromTimeString('18:00:00');
        $this->assertFalse($openingTime->isOpen($time));

        // Check 3rd opening time
        $openingTime = $store->openingTimes->get(2);

        $time = new Carbon();
        $time->next(Carbon::MONDAY);
        $this->assertTrue($openingTime->isOpen($time));

        $time->next(Carbon::FRIDAY);
        $this->assertFalse($openingTime->isOpen($time));
    }

    /**
     * Test if store is open at specific time
     *
     * @return void
     */
    public function testStoreOpening()
    {
        $store = $this->store;

        $tomorrow = Carbon::tomorrow();
        $tomorrow->setTime(13, 30);

        $this->assertTrue($store->isOpen($tomorrow));

        // Check if $store->getOpeningTimes() returns correct number of rows
        $this->assertEquals(3, $store->getOpeningTimes($tomorrow)->count());

        $dayAfterTomorrow = clone $tomorrow;
        $dayAfterTomorrow->modify('+1 day')->setTime(0, 0);

        $this->assertFalse($store->isOpen($dayAfterTomorrow));

        // Check if $store->getOpeningTimes() returns correct number of rows
        $this->assertEquals(2, $store->getOpeningTimes($dayAfterTomorrow)->count());
    }

    public function testStoreOpeningDeliveryCalendar()
    {
        $today = Carbon::now();
        $twoDaysAhead = clone $today;
        $twoDaysAhead->modify('+2 days');

        $threeDaysAhead = clone $today;
        $threeDaysAhead->modify('+3 days');

        $response = $this->json(
            'POST',
            route('catalog.product.availability_calendar'),
            [
                'internal' => TRUE,
                'month' => $today->format('m'),
                'year' => $today->format('Y'),
                'store_id' => $this->store->id,
                'line_items' => [
                    [
                        'line_item_type' => 'product',
                        'quantity' => 2,
                        'line_item_id' => $this->product->id
                    ]
                ]
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment([$twoDaysAhead->format('Y-n-j')])
            ->assertJsonMissing([$threeDaysAhead->format('Y-n-j')]);
    }
}
