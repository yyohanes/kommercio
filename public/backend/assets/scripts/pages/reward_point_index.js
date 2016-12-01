var RewardPointIndex = function () {
    var initTable = function () {
      $('.btn-filter-submit', '#reward-points-dataset').on('click', function(e){
        e.preventDefault();

        var queryString = $(':input', '#reward-points-dataset .filter').map(function () {
          return $(this).val().trim() == "" ? null : this;
        }).serialize();
        window.location.href = $('#reward-points-dataset').data('index') + '?external_filter=1&' + queryString;
      });

      var $dataTable = new Datatable();

      $dataTable.init({
          token: $('#reward-points-dataset').data('form_token'),
          src: $('#reward-points-dataset'),
          filterApplyAction: $('#reward-points-dataset').data('src'),
          filterCancelAction: $('#reward-points-dataset').data('src'),
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
            formBehaviors.init($('#reward-points-dataset tbody'));
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
                  "url": $('#reward-points-dataset').data('src')
              },
              "searching": false,
              "processing": true,
              "serverSide": true,
              "order": [
                  [8, "desc"]
              ],
              "columnDefs": [
                  {"name": "no", "targets": 0, "orderable": false},
                  {"name": "customer", "targets": 1, "orderable": false},
                  {"name": "amount", "targets": 2},
                  {"name": "reason", "targets": 3, "orderable": false},
                  {"name": "type", "targets": 4, "orderable": false},
                  {"name": "status", "targets": 5, "orderable": false},
                  {"name": "created_by", "targets": 6, "orderable": false},
                  {"name": "notes", "targets": 7, "orderable": false},
                  {"name": "created_at", "targets": 8},
                  {"name": "action", "orderable" : false, "targets": 9}
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
  RewardPointIndex.init();
});