var ExportPage = function () {
  var $messageWrapper;

  var createMessagePlaceholder = function ($selector){
    $messageWrapper = $('<div class="well"></div>');

    $messageWrapper.prependTo($selector);
  }

  var appendMessage = function(status, name, notes){
    var $message = null;

    if(status == 'success'){
      $message = '<div><code class="text-success">'+name+': successfully exported.'+'</code></div>';
    }else{
      $message = '<div><code class="text-danger">'+name+': Failed. '+notes+'</code></div>';
    }

    $messageWrapper.append($message);
  }

  var startExport = function($url){
    $.ajax($url, {
      success: function(data){
        if(data.nextUrl && data.nextUrl.length > 0){
          appendMessage(data.row.status, data.row.name, data.row.notes);
          startExport(data.nextUrl);
        }else{
          window.location.href = data.reload;
        }
      }
    });
  }

  return {

    //main function to initiate the module
    init: function ($url, $selector){
      createMessagePlaceholder($selector);
      startExport($url);

      App.blockUI({
        target: '#export-wrapper',
        boxed: true,
        message: 'Exporting...'
      });
    }
  };

}();