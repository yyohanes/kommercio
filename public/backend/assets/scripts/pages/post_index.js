var PostIndex = function () {

    var initTable = function () {
        var $table = new Datatable();

        $table.init({
            token: $('#posts-dataset').data('form_token'),
            src: $('#posts-dataset'),
            filterApplyAction: $('#posts-dataset').data('src'),
            filterCancelAction: $('#posts-dataset').data('src'),
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
                    "url": $('#posts-dataset').data('src')
                },
                "searching": false,
                "processing": true,
                "serverSide": true,
                "order": [
                    [3, "desc"]
                ],
                "columnDefs": [
                    {"name": "id", "targets": 0, "orderable": false},
                    {"name": "name", "targets": 1},
                    {"name": "category", "targets": 2, "orderable": false},
                    {"name": "posts.created_at", "targets": 3},
                    {"name": "active", "targets": 4, "orderable": false},
                    {"name": "action", "orderable" : false, "targets": 5}
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
    PostIndex.init();
});