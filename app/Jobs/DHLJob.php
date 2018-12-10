<?php

namespace Kommercio\Jobs;

use DHL\Datatype\GB\MetaData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DHL\Entity\GB\ShipmentResponse;
use DHL\Entity\GB\ShipmentRequest;
use DHL\Client\Web as WebserviceClient;
use DHL\Datatype\GB\Piece;
use DHL\Datatype\GB\Label;
use Kommercio\Events\DeliveryOrderEvent;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;
use Kommercio\ShippingMethods\DHL;

class DHLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Order */
    protected $order;

    /** @var DeliveryOrder */
    protected $deliveryOrder;

    /** @var Store */
    protected $store;

    /** @var ShippingMethod */
    protected $shippingMethod;

    /** @var array */
    protected $addressConfig;

    /**
     * Create a new job instance.
     *
     * @return void
     * @throws \Exception
     */
    public function __construct(DeliveryOrder $deliveryOrder)
    {
        $this->deliveryOrder = $deliveryOrder;
        $this->order = $deliveryOrder->order;
        $this->store = $this->order->store;
        $this->shippingMethod = ShippingMethod::where('class', 'DHL')->firstOrFail();

        $address = $this->deliveryOrder->shippingProfile->lowestAddress;

        $this->addressConfig = DHL::getConfig($address);

        if (!$this->addressConfig) {
            throw new \Exception('No DHL configuration for ' . $address->name . ' [' . $address->addressType .']');
        };

        if (!$this->deliveryOrder->isShippable) {
            throw new \Exception('Unable to create DHL shipment. Reason: Delivery order is not shippable');
        };

        $this->deliveryOrder->shippingProfile->getDetails();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        $requestReference = $this->order->id . '-' . time();

        $config = [
            'env' => $this->shippingMethod->getData('is_production', false) == '1' ? 'production' : 'staging',
            'request_reference' => md5($requestReference),
            'site_id' => $this->shippingMethod->getData('site_id'),
            'shipper_account_number' => $this->shippingMethod->getData('shipper_account_number'),
            'password' => $this->shippingMethod->getData('password'),
            'company_name' => $this->shippingMethod->getData('company_name'),
            'contact_person' => $this->shippingMethod->getData('contact_person'),
            'contact_number' => $this->shippingMethod->getData('contact_number'),
            'contact_email' => $this->shippingMethod->getData('contact_email'),
        ];

        try {
            $dhlRequest = $this->generateDHLRequest($config);
            $this->logRequest($config['request_reference'], $dhlRequest->toXML());

            // DHL webservice client using the staging environment
            $client = new WebserviceClient($config['env']);
            $xml = $client->call($dhlRequest);

            $this->logResponse($config['request_reference'], $xml);
            $dhlResponse = $this->getDHLResponse($xml);

            $labelFileName = $this->getAndStoreLabel($requestReference, $dhlResponse);

            $this->updateDeliveryOrder($dhlResponse->AirwayBillNumber, $labelFileName);
        } catch (\Throwable $e) {
            \Log::error($e);

            throw $e;
        }
    }

    /**
     * TODO: Move this business logic somewhere DRY
     */
    protected function generateDHLRequest($config = []) {
        $singleProductLineItem = $this->deliveryOrder->lineItems->get(0);
        $warehouse = $this->store->warehouses->first();
        $warehouseCountry = Country::findOrFail($warehouse->country_id);
        $warehouseCountryName = $warehouseCountry->name;
        $warehouseCity = City::find($warehouse->city_id);
        $warehouseDhlConfig = DHL::getConfig($warehouseCountry);

        if ($warehouseCity) {
            $warehouseCityName = $warehouseCity->name;
        } else if ($warehouse->custom_city) {
            $warehouseCityName = $warehouse->custom_city;
        }

        if (!empty($warehouseDhlConfig) && empty($warehouseCityName)) {
            $warehouseCityName = $warehouseDhlConfig['fallbackCityName'];
        }

        if (!empty($warehouseDhlConfig) && !empty($warehouseDhlConfig['dhlName'])) {
            $warehouseCountryName = $warehouseDhlConfig['dhlName'];
        }

        $orderTotal = $this->deliveryOrder->calculateTotalAmount();
        $shippingInformation = $this->deliveryOrder->shippingProfile->fillDetails();

        // Test a ShipmentRequest using DHL XML API
        $request = new ShipmentRequest();

        // Assuming there is a config array variable with id and pass to DHL XML Service
        $request->SiteID = $config['site_id'];
        $request->Password = $config['password'];

        // Set values of the request
        $request->MessageTime = Carbon::now()->toW3cString();
        $request->MessageReference = $config['request_reference'];
        $request->RegionCode = $this->addressConfig['regionCode'];

        // MetaData
        $request->SoftwareName = 'Kommercio';
        $request->SoftwareVersion = '1.1';

        // $request->RequestedPickupTime = 'Y';
        // $request->NewShipper = 'Y';
        $request->LanguageCode = 'en';
        $request->PiecesEnabled = 'Y';
        $request->Billing->ShipperAccountNumber = $config['shipper_account_number'];
        $request->Billing->ShippingPaymentType = 'S';
        // $request->Billing->BillingAccountNumber = $config['billing_account_number'];
        $request->Billing->DutyPaymentType = $this->addressConfig['dutyPaymentType'];
        // $request->Billing->DutyAccountNumber = $config['duty_account_number'];

        $request->Consignee->CompanyName = $shippingInformation->full_name;
        $request->Consignee->addAddressLine($this->formatAddress($shippingInformation->address_1));

        $shippingCountryName = $shippingInformation->country->name;

        if ($shippingInformation->address_2) {
            $request->Consignee->addAddressLine($this->formatAddress($shippingInformation->address_2));
        }

        if ($shippingInformation->city) {
            $cityName = $shippingInformation->city->name;
        } else if ($shippingInformation->custom_city) {
            $cityName = $shippingInformation->custom_city;
        }

        if (!empty($this->addressConfig['overrideCityName'])) {
            $cityName = $this->addressConfig['overrideCityName'];
        }

        if (empty($cityName)) {
            $cityName = $this->addressConfig['fallbackCityName'];
        }

        if (!empty($this->addressConfig['dhlName'])) {
            $shippingCountryName = $this->addressConfig['dhlName'];
        }

        if (!empty($this->addressConfig['countryCode'])) {
            $shippingCountryCode = $this->addressConfig['countryCode'];
        } else {
            $shippingCountryCode = $shippingInformation->country->iso_code;
        }

        $request->Consignee->City = $cityName;
        $request->Consignee->PostalCode = $shippingInformation->postal_code;
        $request->Consignee->CountryCode = $shippingCountryCode;
        $request->Consignee->CountryName = $shippingCountryName;
        $request->Consignee->Contact->PersonName = $shippingInformation->full_name;
        $request->Consignee->Contact->PhoneNumber = $shippingInformation->phone_number;
        $request->Consignee->Contact->Email = $shippingInformation->email;

        $request->Reference->ReferenceID = $this->order->reference;

        $pieces = [];
        foreach ($this->deliveryOrder->lineItems as $lineItem) {
            if (!$lineItem->product) continue;

            $piece = new Piece();
            $piece->PackageType = 'YP';
            $piece->Weight = number_format($lineItem->product->weight / 1000, 2);
            $piece->PieceContents = (string) ($lineItem->quantity + 0);
            $request->ShipmentDetails->addPiece($piece);
            $pieces[] = $piece;
        }

        $contents = [$singleProductLineItem->product->box_content];
        if ($singleProductLineItem->children->count() > 0) {
            foreach ($singleProductLineItem->children as $childLineItem) {
                $childBoxContent = $childLineItem->product->box_content;
                if (empty($childBoxContent)) {
                    $childBoxContent = $childLineItem->product->name;
                }

                $contents[] = sprintf('%sx%d', $childBoxContent, intval($childLineItem->quantity));
            }
        }
        $request->ShipmentDetails->Contents = substr(implode(', ', $contents), 0, 90);
        $request->ShipmentDetails->GlobalProductCode = 'P';
        $request->ShipmentDetails->LocalProductCode = 'P';
        $request->ShipmentDetails->Date = date('Y-m-d');
        $request->ShipmentDetails->NumberOfPieces = count($pieces);
        $request->ShipmentDetails->Weight = number_format($this->deliveryOrder->calculateTotalWeight() / 1000, 2);
        $request->ShipmentDetails->WeightUnit = 'K';
        $request->ShipmentDetails->DoorTo = 'DD';
        $request->ShipmentDetails->DimensionUnit = 'C';
        $request->ShipmentDetails->PackageType = 'YP';
        $request->ShipmentDetails->CurrencyCode = strtoupper($this->order->currency);
        $request->Shipper->ShipperID = $config['shipper_account_number'];
        $request->Shipper->CompanyName = $config['company_name'];
        $request->Shipper->RegisteredAccount = $config['shipper_account_number'];

        $request->Shipper->addAddressLine($this->formatAddress($warehouse->address_1));
        if ($warehouse->address_2) {
            $request->Shipper->addAddressLine($this->formatAddress($warehouse->address_2));
        }

        $request->Shipper->CountryCode = $warehouseCountry->iso_code;
        $request->Shipper->CountryName = $warehouseCountryName;
        $request->Shipper->City = $warehouseCityName;
        $request->Shipper->PostalCode = $warehouse->postal_code;
        $request->Shipper->Contact->PersonName = $config['contact_person'];
        $request->Shipper->Contact->PhoneNumber = $config['contact_number'];

        if ($config['contact_email']) {
            $request->Shipper->Contact->Email = $config['contact_email'];
        }

        $dutiable = $orderTotal >= $this->addressConfig['dutiableMinimum'];

        $request->ShipmentDetails->IsDutiable = $dutiable ? 'Y' : 'N';
        $request->Dutiable->DeclaredValue = number_format($orderTotal, 2);
        $request->Dutiable->DeclaredCurrency = strtoupper($this->order->currency);
        $request->Dutiable->TermsOfTrade = 'DDP';

        $request->EProcShip = 'N';
        $request->LabelImageFormat = 'PDF';
        $request->Label = new Label();
        $request->Label->LabelTemplate = '8X4_PDF';

        // $request->RequestArchiveDoc = 'Y';
        // $request->NumberOfArchiveDoc = 2;

        return $request;
    }

    /**
     * Create Delivery Order of an order
     * @param string $awbNumber
     * @param string $awbLabelFile
     * @throws \Exception
     */
    protected function updateDeliveryOrder(string $awbNumber, string $awbLabelFile)
    {
        $deliveryOrderData = [
            'tracking_number' => $awbNumber,
            'delivered_by' => $this->shippingMethod->name,
            'dhl_awb_label' => $awbLabelFile,
        ];

        $this->deliveryOrder->saveData($deliveryOrderData, true);
    }

    /**
     * Convert response from DHL request to XML
     * @param string $xml
     * @return ShipmentResponse
     * @throws \Exception
     */
    protected function getDHLResponse(string $xml)
    {
        $response = new ShipmentResponse();
        $response->initFromXML($xml);

        return $response;
    }

    /**
     * Store XML request for logging
     * @param string $messageId
     * @param string $xmlString
     */
    protected function logRequest(string $messageId, string $xmlString) {
        Storage::put('dhl/logs/requests/' . $messageId . '.xml', $xmlString);
    }

    /**
     * Store XML response for logging
     * @param string $messageId
     * @param string $xmlString
     */
    protected function logResponse(string $messageId, string $xmlString) {
        Storage::put('dhl/logs/responses/' . $messageId . '.xml', $xmlString);
    }

    /**
     * @param string $requestReference
     * @param ShipmentResponse $response
     * @return null|string
     */
    protected function getAndStoreLabel(string $requestReference, ShipmentResponse $response)
    {
        $fileName = 'dhl/labels/' . $requestReference . '.pdf';
        $pdfContent = base64_decode($response->LabelImage->OutputImage);

        try {
            Storage::put($fileName, $pdfContent);
        } catch (\Throwable $e) {
            \Log::error($e);
            $fileName = null;
        }

        return $fileName;
    }

    /**
     * Format address to comply with DHL chars length limit
     * @param string $address
     * @return bool|string
     */
    protected function formatAddress($address) {
        $max = 35;
        return substr($address, 0, $max);
    }

    /**
     * Format shipment contents to comply with DHL chars length limit
     * @param string $content
     * @return bool|string
     */
    protected function formatContents($content) {
        $max = 90;
        return substr($content, 0, $max);
    }
}
