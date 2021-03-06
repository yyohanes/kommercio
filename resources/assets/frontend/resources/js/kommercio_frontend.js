const KommercioFrontend = function(){
  var loadedScripts = [];

  function isFunction(x) {
    return Object.prototype.toString.call(x) == '[object Function]';
  }

  return {
    init: function () {
      this.csrfHeaderSetup(global_vars.csrf_token);
      this.confirmBtns(document);
      this.popupBtns(document);

      $(document).ajaxComplete(function (event, request, settings) {
        if (typeof request.responseJSON !== 'undefined' && typeof request.responseJSON._token !== 'undefined') {
          KommercioFrontend.csrfHeaderSetup(request.responseJSON._token);

          $('input[name="_token"]').val(request.responseJSON._token);
        }
      });
    },
    confirmBtns: function(context){
      $('[data-confirm]', context).each(function(idx, obj){
        var $message = $(obj).data('confirm');

        $(obj).on('click', function(){
          return confirm($message);
        });
      });
    },
    popupBtns: function(context){
      $('.popup-btn', context).on('click', function(e){
        e.preventDefault();

        var width = $(this).data('popup_width')?$(this).data('popup_width'):720;
        var height = $(this).data('popup_height')?$(this).data('popup_width'):560;

        window.open($(this).attr('href'), 'popup-window', 'width='+width+',height='+height);
      });
    },
    csrfHeaderSetup: function (token) {
      $.ajaxPrefilter(function (options, originalOptions, xhr) { // this will run before each request
        if (token) {
          return xhr.setRequestHeader('X-CSRF-TOKEN', global_vars.csrf_token); // adds directly to the XmlHttpRequest Object
        }
      });
    },
    selectHelper: {
      convertToOptions: function (data, $first_option) {
        var $return = '';

        if (typeof $first_option !== 'undefined') {
          $return += '<option value="">' + $first_option + '</option>';
        }

        for (var val in data) {
          $return += '<option value="' + val + '">' + data[val] + '</option>';
        }

        return $return;
      }
    },
    convertDotToSquareBracket: function (name, forSelector) {
      var parts = String(name).split('.');
      var returnText = '';

      for (var i in parts) {
        if (i != 0) {
          returnText += (forSelector ? '\\[' : '[') + parts[i] + (forSelector ? '\\]' : ']');
        } else {
          returnText += parts[i];
        }
      }

      return returnText;
    },
    addError: function ($name, $message, context) {
      if (typeof context === 'undefined') {
        context = document;
      }

      $('[name="' + KommercioFrontend.convertDotToSquareBracket($name, true) + '"]', context).parent().addClass('has-error').append('<div class="help-block">' + $message + '</div>');
    },
    clearErrors: function (context) {
      $('.has-error', context).removeClass('has-error');
      $('.help-block', context).remove();
      $('.alert-danger', context).remove();
    },
    toggleOverlay: function($to, $toggleTo)
    {
      if($toggleTo){
        if($to.find('.loading-overlay').length == 0){
          $to.append('<div class="loading-overlay" />');
        }
      }else{
        $to.find('.loading-overlay').remove();
      }
    },
    loadJSScript: function(path, onLoad) {
      if(loadedScripts.indexOf(path) < 0){
        $.getScript(path)
            .done(function() {
              loadedScripts.push(path);

              if($.isFunction(onLoad)){
                onLoad();
              }
            })
            .fail(function() {
              /* boo, fall back to something else */
            });
      }else{
        if($.isFunction(onLoad)){
          onLoad();
        }
      }
    },
    runtimeObjects: {}
  }
}();

export default KommercioFrontend;