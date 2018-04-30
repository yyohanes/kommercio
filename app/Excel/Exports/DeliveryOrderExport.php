<?php

namespace Kommercio\Excel\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Kommercio\Facades\ProjectHelper;

class DeliveryOrderExport implements FromView {
    use Exportable;

    protected $deliveryOrder;

    public function __construct($options)
    {
        $this->deliveryOrder = $options['deliveryOrder'];
    }

    public function view(): View
    {
        return view(ProjectHelper::getViewTemplate('print.excel.order.delivery_order'), [
            'deliveryOrder' => $this->deliveryOrder,
        ]);
    }
}
