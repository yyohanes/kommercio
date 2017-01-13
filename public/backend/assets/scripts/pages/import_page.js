var ImportPage = function () {
  var $messageWrapper;

  var createMessagePlaceholder = function ($selector){
    $messageWrapper = $('<div class="well"></div>');

    $messageWrapper.prependTo($selector);
  }

  var appendMessage = function(status, name, notes){
    var $message = null;

    if(status == 'success'){
      $message = '<div><code class="text-success">'+name+': Successfully imported.'+'</code></div>';
    }else{
      $message = '<div><code class="text-danger">'+name+': Failed. '+notes+'</code></div>';
    }

    $messageWrapper.append($message);
  }

  var startImport = function($url){
    $.ajax($url, {
      success: function(data){
        if(data.nextUrl && data.nextUrl.length > 0){
          appendMessage(data.row.status, data.row.name, data.row.notes);
          startImport(data.nextUrl);
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
      startImport($url);

      App.blockUI({
        target: '#import-wrapper',
        boxed: true,
        message: 'Importing...'
      });
    }
  };

}();