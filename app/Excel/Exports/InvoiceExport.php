<?php

namespace Kommercio\Excel\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Kommercio\Facades\ProjectHelper;

class InvoiceExport implements FromView {
    use Exportable;

    protected $order;

    public function __construct($options)
    {
        $this->order = $options['order'];
    }

    public function view(): View
    {
        return view(ProjectHelper::getViewTemplate('print.excel.order.invoice'), [
            'order' => $this->order,
        ]);
    }
}
