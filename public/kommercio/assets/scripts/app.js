var KommercioFrontend = function(){
    return {
        init: function(){
            this.csrfHeaderSetup(global_vars.csrf_token);

            $(document).ajaxComplete(function( event,request, settings ) {
                if(typeof request.responseJSON !== 'undefined' && typeof request.responseJSON._token !== 'undefined'){
                    KommercioFrontend.csrfHeaderSetup(request.responseJSON._token);

                    $('input[name="_token"]').val(request.responseJSON._token);
                }
            });
        },
        csrfHeaderSetup: function(token){
            $.ajaxPrefilter(function(options, originalOptions, xhr) { // this will run before each request
                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', global_vars.csrf_token); // adds directly to the XmlHttpRequest Object
                }
            });
        },
        selectHelper: {
            convertToOptions: function(data, $first_option){
                $return = '';

                if(typeof $first_option !== 'undefined'){
                    $return += '<option value="">' + $first_option + '</option>';
                }

                for(val in data){
                    $return += '<option value="' + val + '">' + data[val] + '</option>';
                }

                return $return;
            }
        }
    }
}();

jQuery(document).ready(function() {
    KommercioFrontend.init();
});