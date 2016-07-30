var OrderIndex = function () {
    var runtimeAdditonalColumns = 0;
    var initTable = function () {
        var $dataTable = new Datatable();

        var columnDefs = [
            {"name": "no", "targets": 0, "orderable": false},
            {"name": "reference", "targets": 1},
            {"name": "checkout_at", "targets": 2}
        ];

        if(enable_delivery_date){
            columnDefs.push({"name": "delivery_date", "targets": 3});
            runtimeAdditonalColumns += 1;
        }

        columnDefs = columnDefs.concat([
            {"name": "billing", "targets": 3+runtimeAdditonalColumns, "orderable": false},
            {"name": "shipping", "targets": 4+runtimeAdditonalColumns, "orderable": false}
        ]);

        for(var i=0; i<additional_columns;i+=1){
            columnDefs.push({"name": "sticky_product"+i, "targets": 5+i+runtimeAdditonalColumns, "orderable": false});
        }
        runtimeAdditonalColumns += additional_columns;

        columnDefs = columnDefs.concat([
            {"name": "total", "targets": 5+runtimeAdditonalColumns},
            {"name": "outstanding", "targets": 6+runtimeAdditonalColumns},
            {"name": "status", "targets": 7+runtimeAdditonalColumns, "orderable": false}
        ]);

        if(show_store_column){
            columnDefs.push({"name": "store_id", "orderable" : false, "targets": 8+runtimeAdditonalColumns});
            runtimeAdditonalColumns += 1;
        }

        columnDefs.push({"name": "action", "orderable" : false, "targets": 8+runtimeAdditonalColumns});

        $dataTable.init({
            token: $('#orders-dataset').data('form_token'),
            src: $('#orders-dataset'),
            filterApplyAction: $('#orders-dataset').data('src'),
            filterCancelAction: $('#orders-dataset').data('src'),
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
                formBehaviors.init($('#orders-dataset tbody'));
            },
            loadingMessage: 'Loading...',
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                // So when dropdowns used the scrollable div should be removed.
                //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",

                "bStateSave": false,

                "lengthMenu": [
                    [50, 100, 150, 200, -1],
                    [50, 100, 150, 200, "All"] // change per page values here
                ],
                "pageLength": 50, // default record count per page
                "ajax": {
                    "url": $('#orders-dataset').data('src')
                },
                "searching": false,
                "processing": true,
                "serverSide": true,
                "order": [
                    [2, "desc"]
                ],
                "columnDefs": columnDefs
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
    OrderIndex.init();
});