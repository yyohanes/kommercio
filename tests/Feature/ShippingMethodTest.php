<?php

namespace Tests\Feature;

use TestCase;
use Kommercio\Models\Store;
use Kommercio\Models\Product;
use Kommercio\Models\ProductDetail;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class ShippingMethodTest extends TestCase
{
    /**
     * Variable to store dummy order
     * @var Order
     */
    protected $order;

    /**
     * Variable to store dummy products
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $products;

    /**
     * Variable to store dummy store
     * @var Store
     */
    protected $store;

    /**
     * Variable to store tested delivery shippingMethod
     * @var ShippingMethod
     */
    protected $deliveryShippingMethod;

    /**
     * Variable to store tested pick-up shippingMethod
     * @var ShippingMethod
     */
    protected $pickUpShippingMethod;

    /**
     * Variable to store tested COD paymentMethod
     * @var PaymentMethod
     */
    protected $codPaymentMethod;

    /**
     * Variable to store tested BankTransfer paymentMethod
     * @var PaymentMethod
     */
    protected $wirePaymentMethod;

    protected function setUp() {
      parent::setUp();

      $this->codPaymentMethod = PaymentMethod::create([
          'name' => 'Cash on Delivery',
          'class' => 'CashOnDelivery',
          'active' => TRUE
      ]);

      $this->wirePaymentMethod = PaymentMethod::create([
          'name' => 'BankTransfer',
          'class' => 'BankTransfer',
          'active' => TRUE
      ]);

      $this->deliveryShippingMethod = ShippingMethod::create([
          'name' => 'Example Delivery',
          'class' => 'ExampleShipping',
          'active' => TRUE
      ]);

      $this->pickUpShippingMethod = ShippingMethod::create([
          'name' => 'Pick Up',
          'class' => 'PickUp',
          'active' => TRUE
      ]);

      // Setup order to be tested
      $this->store = factory(Store::class)->create();

      $this->order = factory(Order::class)->create([
          'store_id' => $this->store->id
      ]);

      $this->products = factory(Product::class, 3)->create();

      foreach ($this->products as $product) {
          factory(ProductDetail::class)->create([
              'store_id' => $this->store->id,
              'product_id' => $product->id
          ]);

          $product->load('productDetails');

          $this->order->addToCart($product, rand(1, 3));
      }

      $this->order->paymentMethod()->associate($this->codPaymentMethod);
      $this->order->save();
    }

    protected function tearDown() {
        $this->deliveryShippingMethod->delete();
        $this->pickUpShippingMethod->delete();
        $this->codPaymentMethod->delete();
        $this->wirePaymentMethod->delete();
        $this->order->forceDelete();

        foreach ($this->products as $product) {
            $product->forceDelete();
        }

        $this->store->delete();
    }

    /**
     * Test if returned shippingMethods is successfully filtered by payment methods
     * @return void
     */
    public function testShippingMethodsByPaymentMethod() {
        $allMethodsCount = count($this->pickUpShippingMethod->getProcessor()->getAvailableMethods()) + count($this->deliveryShippingMethod->getProcessor()->getAvailableMethods());

        $this->pickUpShippingMethod->paymentMethods()->attach($this->codPaymentMethod);
        $this->pickUpShippingMethod->load('paymentMethods');

        // Test by order
        $shippingMethods = ShippingMethod::getShippingMethods([
            'order' => $this->order
        ]);

        $assertedMethodCounts = count($this->pickUpShippingMethod->getProcessor()->getAvailableMethods());

        $this->assertCount($assertedMethodCounts, $shippingMethods);

        // Test by request
        $dummyRequest = new \Illuminate\Http\Request();
        $dummyRequest->setMethod('POST');
        $dummyRequest->request->set('payment_method', $this->codPaymentMethod->id);

        $shippingMethods = ShippingMethod::getShippingMethods([
            'request' => $dummyRequest,
            'order' => $this->order
        ]);

        $this->assertCount($assertedMethodCounts, $shippingMethods);

        // Test the other payment method
        $this->order->paymentMethod()->associate($this->wirePaymentMethod);
        $this->order->save();
        $this->order->load('paymentMethod');

        $shippingMethods = ShippingMethod::getShippingMethods([
            'order' => $this->order
        ]);

        $this->assertCount($allMethodsCount, $shippingMethods);

        // Test one payment method with 2 shippings
        $this->order->paymentMethod()->associate($this->wirePaymentMethod);
        $this->order->save();
        $this->order->load('paymentMethod');

        $this->wirePaymentMethod->shippingMethods()->sync([$this->pickUpShippingMethod->id, $this->deliveryShippingMethod->id]);
        $this->wirePaymentMethod->load('shippingMethods');

        $shippingMethods = ShippingMethod::getShippingMethods([
            'order' => $this->order
        ]);

        $this->assertCount($allMethodsCount, $shippingMethods);

        // When paymentMethod is null
        $this->order->paymentMethod()->dissociate();
        $this->order->save();

        $shippingMethods = ShippingMethod::getShippingMethods([
            'order' => $this->order
        ]);

        $this->assertCount($allMethodsCount, $shippingMethods);

        // When show_all flag is true
        $shippingMethods = ShippingMethod::getShippingMethods([
            'order' => $this->order,
            'show_all_active' => TRUE
        ]);

        $this->assertCount($allMethodsCount, $shippingMethods);
    }

    /**
     * Test if returned paymentMethods is successfully filtered by shipping method
     * @return void
     */
    public function testPaymentMethodsByShippingMethod() {
        // When shippingMethod is null
        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $this->order
        ]);

        $this->assertCount(2, $paymentMethods);

        // When pick-up can be paid with both COD and Bank Transfer
        $this->pickUpShippingMethod->paymentMethods()->sync([$this->codPaymentMethod->id, $this->wirePaymentMethod->id]);
        $this->pickUpShippingMethod->load('paymentMethods');

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $this->order
        ]);

        $this->assertCount(2, $paymentMethods);

        // When shippingMethod is PickUp
        $pickUpShippingMethods = $this->pickUpShippingMethod->getPrices();
        $this->order->updateShippingMethod(array_keys($pickUpShippingMethods)[0], array_shift($pickUpShippingMethods));

        $this->deliveryShippingMethod->paymentMethods()->sync([]);
        $this->deliveryShippingMethod->load('paymentMethods');

        $this->pickUpShippingMethod->paymentMethods()->sync([$this->codPaymentMethod->id]);
        $this->pickUpShippingMethod->load('paymentMethods');

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $this->order
        ]);

        $this->assertCount(1, $paymentMethods);

        // When show_all flag is true
        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $this->order,
            'show_all_active' => TRUE
        ]);

        $this->assertCount(2, $paymentMethods);
    }
}
