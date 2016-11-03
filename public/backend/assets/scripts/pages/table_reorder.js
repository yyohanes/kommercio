var DatasetReorder = function () {
    var getSelectedRowsCount = function(table) {
            return $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        }

    var getSelectedRows = function(table) {
        var rows = [];
        $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).each(function() {
            rows.push($(this).val());
        });

        return rows;
    }

    var countSelectedRecords = function(table, tableWrapper) {
        var selected = $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        var text = '_TOTAL_ records selected:  ';
        if (selected > 0) {
            $('.table-group-actions > span', tableWrapper).text(text.replace("_TOTAL_", selected));
        } else {
            $('.table-group-actions > span', tableWrapper).text("");
        }
    };

    var initTable = function () {
        $('.dataset-reorder').each(function(idx, obj){
            var $table = $(obj);
            var $rowClass = $(obj).data('row_class');
            var $rowValue = $(obj).data('row_value');
            var $dataset = $table.DataTable({
                ordering: false,
                lengthMenu: [[50, 100, -1], [50, 100, 'All']],
                pageLength: -1,
                rowReorder: {
                    selector: 'tr',
                    update: false,
                    snapX: false
                }
            });

            $dataset.on( 'row-reorder', function ( e, details, changes ) {
                var newOrders = [];

                $(obj).find('.'+$rowClass).each(function(idy, objy){
                    newOrders.push($(objy).data($rowValue));
                });

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
            var $tableWrapper = $(obj).parents('.dataset-wrapper');
            var $paging = typeof $table.data('dataset-paging') !== 'undefined'?$table.data('dataset-paging'):true;
            var $dataTable = $table.DataTable({
                ordering: false,
                paging: $paging
            });

            if($table.find('.group-checkable').length > 0){
                $table.find('.group-checkable').change(function () {
                    var set = jQuery(this).attr("data-set");
                    var checked = jQuery(this).is(":checked");
                    jQuery(set).each(function () {
                        if (checked) {
                            $(this).prop("checked", true);
                            $(this).parents('tr').addClass("active");
                        } else {
                            $(this).prop("checked", false);
                            $(this).parents('tr').removeClass("active");
                        }
                    });
                    jQuery.uniform.update(set);
                });

                $table.on('change', 'tbody tr .checkboxes', function () {
                    countSelectedRecords($table, $tableWrapper);

                    $(this).parents('tr').toggleClass("active");
                });
            }

            // handle group actionsubmit button click
            if($tableWrapper.find('.table-group-action-submit').length > 0){
                $tableWrapper.on('click', '.table-group-action-submit', function (e) {
                    e.preventDefault();
                    var $modal = '#ajax_modal';
                    var action = $(".table-group-action-input", $tableWrapper);
                    if (action.val() != "" && getSelectedRowsCount($table) > 0) {
                        $.ajax($(".table-group-action-input", $tableWrapper).data('bulk_action'), {
                            method: 'POST',
                            data: {
                                'id': getSelectedRows($table),
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
                                    container: $tableWrapper,
                                    place: 'prepend'
                                });
                            }
                        });
                    } else if (action.val() == "") {
                        App.alert({
                            type: 'danger',
                            icon: 'warning',
                            message: 'Please select an action',
                            container: $tableWrapper,
                            place: 'prepend'
                        });
                    } else if (getSelectedRowsCount($table) === 0) {
                        App.alert({
                            type: 'danger',
                            icon: 'warning',
                            message: 'No order selected',
                            container: $tableWrapper,
                            place: 'prepend'
                        });
                    }
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
    DatasetReorder.init();
});