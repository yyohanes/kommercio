var OrderIndex = function () {
    $('#order-filter-btn').on('click', function(e){
        e.preventDefault();

        var queryString = $(':input', '#order-filter-form').map(function () {
            return $(this).val().trim() == "" ? null : this;
        }).serialize();
        window.location.href = $('#order-filter-form').data('order_index') + '?external_filter=1&' + queryString;
    });

    var runtimeAdditonalColumns = 0;
    var initTable = function () {
        var $dataTable = new Datatable();

        var columnDefs = [
            {"name": "bulk_action", "targets": 0, "orderable": false},
            {"name": "action", "targets": 1, "orderable": false},
            {"name": "no", "targets": 2, "orderable": false},
            {"name": "reference", "targets": 3},
            {"name": "checkout_at", "targets": 4}
        ];

        if(enable_delivery_date){
            columnDefs.push({"name": "delivery_date", "targets": 5});
            runtimeAdditonalColumns += 1;
        }

        columnDefs = columnDefs.concat([
            {"name": "billing", "targets": 5+runtimeAdditonalColumns, "orderable": false},
            {"name": "shipping", "targets": 6+runtimeAdditonalColumns, "orderable": false}
        ]);

        for(var i=0; i<additional_columns;i+=1){
            columnDefs.push({"name": "sticky_product"+i, "targets": 7+i+runtimeAdditonalColumns, "orderable": false});
        }
        runtimeAdditonalColumns += additional_columns;

        columnDefs = columnDefs.concat([
            {"name": "total", "targets": 7+runtimeAdditonalColumns}
        ]);

        if(view_payment){
            columnDefs.push({"name": "payment_method", "orderable" : false, "targets": 8+runtimeAdditonalColumns});
            columnDefs.push({"name": "outstanding", "targets": 9+runtimeAdditonalColumns});
            runtimeAdditonalColumns += 1;
        }

        columnDefs = columnDefs.concat([
            {"name": "status", "targets": 9+runtimeAdditonalColumns, "orderable": false}
        ]);

        if(show_store_column){
            columnDefs.push({"name": "store_id", "orderable" : false, "targets": 10+runtimeAdditonalColumns});
            runtimeAdditonalColumns += 1;
        }

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

                "bStateSave": true,

                "lengthMenu": [
                    [10, 20, 50, 100, 150, 200, -1],
                    [10, 20, 50, 100, 150, 200, "All"] // change per page values here
                ],
                "pageLength": 50, // default record count per page
                "ajax": {
                    "url": $('#orders-dataset').data('src'),
                    "timeout": 0
                },
                "searching": false,
                "processing": true,
                "serverSide": true,
                "order": [
                    [4, "desc"]
                ],
                "columnDefs": columnDefs

                /*//Scroller
                scrollY: 450,
                deferRender: true,
                scroller: true*/
            }
        });

        // handle group actionsubmit button click
        var grid = $dataTable;
        grid.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
            e.preventDefault();
            var $modal = '#ajax_modal';
            var action = $(".table-group-action-input", grid.getTableWrapper());
            if (action.val() != "" && grid.getSelectedRowsCount() > 0) {
                $.ajax($(".table-group-action-input", grid.getTableWrapper()).data('bulk_action'), {
                    method: 'POST',
                    data: {
                        'id': grid.getSelectedRows(),
                        'action': action.val(),
                        'backUrl': global_vars.current_path
                    },
                    success: function(data){
                        var $loadedData = $(data);

                        $($modal).find('.modal-content').html($loadedData);

                        $($modal).modal('show');

                        formBehaviors.init($($modal).find('.modal-content'));
                        App.initAjax();
                    },
                    error: function(xhr){
                        App.alert({
                            type: 'danger',
                            icon: 'warning',
                            message: xhr.responseText,
                            container: grid.getTableWrapper(),
                            place: 'prepend'
                        });
                    }
                });
            } else if (action.val() == "") {
                App.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: 'Please select an action',
                    container: grid.getTableWrapper(),
                    place: 'prepend'
                });
            } else if (grid.getSelectedRowsCount() === 0) {
                App.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: 'No order selected',
                    container: grid.getTableWrapper(),
                    place: 'prepend'
                });
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