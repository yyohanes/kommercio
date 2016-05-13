var DatasetReorder = function () {

    var initTable = function () {
        $('.dataset-reorder').each(function(idx, obj){
            var $table = $(obj);
            var $rowClass = $(obj).data('row_class');
            var $rowValue = $(obj).data('row_value');
            var $dataset = $table.DataTable({
                ordering: false,
                dom: "<'row'<'col-sm-12'f>r><'table-scrollable't><'row'<'col-sm-12'i>>", // horizobtal scrollable datatable
                rowReorder: {
                    selector: '.fa-reorder',
                    update: false,
                    snapX: true
                }
            });

            $dataset.on( 'row-reorder', function ( e, details, changes ) {
                var newOrders = [];

                for(var i in details){
                    newOrders.push($(details[i].node).find('.'+$rowClass).data($rowValue));
                }

                if(newOrders.length > 0){
                    App.blockUI({
                        target: $table,
                        boxed: true,
                        message: 'Saving new order...'
                    });

                    jQuery.ajax(
                        $table.data('reorder_action'),
                        {
                            method: 'POST',
                            data: {
                                '_token': $table.data('form_token'),
                                'objects': newOrders
                            },
                            success: function(data){
                                App.unblockUI($table);
                            },
                            error: function(data){
                                KommercioApp.errorPopup();
                            }
                        }
                    );
                }
            });
        });

        $('.dataset-table').each(function(idx, obj){
            var $table = $(obj);
            var $dataset = $table.DataTable({
                ordering: false,
                dom: "<'row'<'col-sm-12'f>r><'table-scrollable't><'row'<'col-sm-12'i>>"
            });
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
    DatasetReorder.init();
});