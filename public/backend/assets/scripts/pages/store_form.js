var StoreForm = function () {
  var $operatingHourTemplate = $('#operating-hour-template').html();
  var $operatingHourPrototype = Handlebars.compile($operatingHourTemplate);

  var handleOperatingHours = function () {
    if (!$().sortable) {
      return;
    }

    $('#add-schedule-btn').on('click', function(e){
      e.preventDefault();

      var $newOperatingHourRow = $($operatingHourPrototype({
        idx: $('.portlet', '#openingTimes_accordion').length
      }));

      $newOperatingHourRow.find('.make-switch').bootstrapSwitch();
      formBehaviors.init($newOperatingHourRow);

      $('#openingTimes_accordion').append($newOperatingHourRow);

      $('#openingTimes_accordion').sortable('reload');
    });

    $('#openingTimes_accordion').sortable({
      placeholder: '<div></div>'
    });
  };

  return {
    init: function () {
      handleOperatingHours();
    }
  };
}();

jQuery(document).ready(function() {
  StoreForm.init();
});