<?php

namespace Kommercio\Excel\Exports;

use Illuminate\Contracts\View\View;
use Kommercio\Facades\ProjectHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class DeliveryReportExport implements FromView {
    use Exportable;

    protected $filter;
    protected $orders;
    protected $shippingMethod;
    protected $date;
    protected $dateType;
    protected $orderedProducts;

    public function __construct($options)
    {
        $this->filter = $options['filter'];
        $this->orders = $options['orders'];
        $this->shippingMethod = $options['shippingMethod'];
        $this->date = $options['date'];
        $this->dateType = $options['dateType'];
        $this->includedProducts = $options['includedProducts'];
    }

    public function view(): View
    {
        return view(ProjectHelper::getViewTemplate('backend.report.export.xls.delivery'), [
            'filter' => $this->filter,
            'orders' => $this->orders,
            'shippingMethod' => $this->shippingMethod,
            'date' => $this->date,
            'dateType' => $this->dateType,
            'includedProducts' => $this->includedProducts,
        ]);
    }
}
