var KommercioApp = function(){
  var handleFixedHeaderTable = function() {
    var fixedHeaderOffset = 0;
    if (App.getViewPort().width < App.getResponsiveBreakpoint('md')) {
      if ($('.page-header').hasClass('page-header-fixed-mobile')) {
        fixedHeaderOffset = $('.page-header').outerHeight(true);
      }
    } else if ($('.page-header').hasClass('navbar-fixed-top')) {
      fixedHeaderOffset = $('.page-header').outerHeight(true);
    }

    $('.fixed-header-table').each(function(idx, obj){
      $(obj).floatThead('destroy');

      $(obj).floatThead({
        position: 'absolute',
        top: fixedHeaderOffset,
        responsiveContainer: function($table){
          return $table.closest(".table-responsive");
        }
      });
    });
  }

  var convertSuccessMessagesToGrowl = function(){
    $('.alert-success').each(function(idx, obj){
      $.bootstrapGrowl($(obj).text(), {
        ele: 'body', // which element to append to
        type: 'success', // (null, 'info', 'danger', 'success')
        offset: {from: 'top', amount: 20}, // 'top', or 'bottom'
        align: 'right', // ('left', 'right', or 'center')
        width: 250, // (integer, or 'auto')
        delay: 4000, // Time while the message will be displayed. It's not equivalent to the *demo* timeOut!
        allow_dismiss: true, // If true then will display a cross to close the popup.
        stackup_spacing: 10 // spacing between consecutively stacked growls.
      });

      $(obj).remove();
    });
  }

  return {
    init: function(){
      this.csrfHeaderSetup(global_vars.csrf_token);

      $(document).ajaxComplete(function( event,request, settings ) {
        if(typeof request.responseJSON !== 'undefined' && typeof request.responseJSON._token !== 'undefined'){
          console.log(request.responseJSON);
          KommercioApp.csrfHeaderSetup(request.responseJSON._token);

          $('input[name="_token"]').val(request.responseJSON._token);
        }
      });

      convertSuccessMessagesToGrowl();
      handleFixedHeaderTable();
    },
    errorPopup: function(message){
      if(typeof message === 'undefined'){
        message = 'There is an error in this process. Please reload this page.';
      }

      alert(message);
    },
    csrfHeaderSetup: function(token){
      $.ajaxPrefilter(function(options, originalOptions, xhr) { // this will run before each request
        if (token) {
          return xhr.setRequestHeader('X-CSRF-TOKEN', global_vars.csrf_token); // adds directly to the XmlHttpRequest Object
        }
      });
    }
  }
}();

jQuery(document).ready(function() {
  KommercioApp.init();
});