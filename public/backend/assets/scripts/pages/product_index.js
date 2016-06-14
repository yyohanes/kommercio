var ProductIndex = function () {

    var initTable = function () {
        var $productTable = new Datatable();

        $productTable.init({
            token: $('#products-dataset').data('form_token'),
            src: $('#products-dataset'),
            filterApplyAction: $('#products-dataset').data('src'),
            filterCancelAction: $('#products-dataset').data('src'),
            onSuccess: function (grid, response) {
                // grid:        grid object
                // response:    json object of server side ajax response
                // execute some code after table records loaded
            },
            onError: function (grid) {
                // execute some code on network or other general error
            },
            onDataLoad: function(grid) {
                // execute some code on ajax data load
                App.initComponents();
            },
            loadingMessage: 'Loading...',
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                // So when dropdowns used the scrollable div should be removed.
                //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",

                "bStateSave": true,

                "lengthMenu": [
                    [50, 100, 150, 200, -1],
                    [50, 100, 150, 200, "All"] // change per page values here
                ],
                "pageLength": 50, // default record count per page
                "ajax": {
                    "url": $('#products-dataset').data('src')
                },
                "searching": false,
                "processing": true,
                "serverSide": true,
                "order": [
                    [0, "asc"]
                ],
                "columnDefs": [
                    {"name": "sort_order", "targets": 0, "orderable": true},
                    {"name": "image", "targets": 1, "orderable": false},
                    {"name": "name", "targets": 2},
                    {"name": "sku", "targets": 3},
                    {"name": "category", "targets": 4, "orderable": false},
                    {"name": "manufacturer", "targets": 5, "orderable": false},
                    {"name": "retail_price", "targets": 6},
                    {"name": "net_price", "targets": 7, "orderable": false},
                    {"name": "active", "targets": 8, "orderable": false},
                    {"name": "products.created_at", "targets": 9},
                    {"name": "action", "orderable" : false, "targets": 10}
                ]
            }
        });
    }

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            initTable();
        }

    };

}();

jQuery(document).ready(function() {
    ProductIndex.init();
});