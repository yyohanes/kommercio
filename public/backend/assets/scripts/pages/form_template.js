var formBehaviors = function(){
    var handleBtnLinks = function(context){
        $('button[href]', context).on('click', function(e){
            e.preventDefault();

            window.location.href = this.getAttribute('href');
        });
    }

    var handleSummernote = function (context) {
        $('.summernote-editor', context).each(function(idx, obj){
            $(obj).summernote({
                height: ($(obj).data('height') === undefined)?200:$(obj).data('height'),
                disableDragAndDrop: true,
                onPaste: function(e){
                    var updatePastedText = function(someNote){
                        var original = someNote.code();
                        var cleaned = CleanPastedHTML(original); //this is where to call whatever clean function you want. I have mine in a different file, called CleanPastedHTML.
                        someNote.code(cleaned); //this sets the displayed content editor to the cleaned pasted code.
                    };
                    setTimeout(function () {
                        //this kinda sucks, but if you don't do a setTimeout,
                        //the function is called before the text is really pasted.
                        updatePastedText($(obj));
                    }, 100);
                }
            });

            $(obj).parents('form').on('submit', function(){
                if($(obj).summernote('isEmpty')){
                    $(obj).val('');
                }
            });
        });

        var CleanPastedHTML = function(input) {
            // 1. remove line breaks / Mso classes
            var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
            var output = input.replace(stringStripper, ' ');
            // 2. strip Word generated HTML comments
            var commentSripper = new RegExp('<!--(.*?)-->','g');
            var output = output.replace(commentSripper, '');
            var tagStripper = new RegExp('<(/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>','gi');
            // 3. remove tags leave content if any
            output = output.replace(tagStripper, '');
            // 4. Remove everything in between and including tags '<style(.)style(.)>'
            var badTags = ['style', 'script','applet','embed','noframes','noscript'];

            for (var i=0; i< badTags.length; i++) {
                tagStripper = new RegExp('<'+badTags[i]+'.*?'+badTags[i]+'(.*?)>', 'gi');
                output = output.replace(tagStripper, '');
            }
            // 5. remove attributes ' style="..."'
            var badAttributes = ['style', 'start'];
            for (var i=0; i< badAttributes.length; i++) {
                var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"','gi');
                output = output.replace(attributeStripper, '');
            }

            return output;
        }
    }

    var handleSelects = function(context){
        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".select2, .select2-multiple", context).select2({
            width: null
        });

        $(".select2-ajax", context).each(function(idx, obj){
            $(obj).select2({
                width: "off",
                ajax: {
                    url: $(obj).data('remote_source'),
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            query: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function(data, page) {
                        // parse the results into the format expected by Select2.
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data
                        return {
                            results: data.data
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                minimumInputLength: 2,
                templateResult: function(repo){
                    return repo[$(obj).data('remote_label_property')];
                },
                templateSelection: function(repo){
                    return repo[$(obj).data('remote_value_property')] || repo.text;
                }
            });
        });

        // copy Bootstrap validation states to Select2 dropdown
        //
        // add .has-warning, .has-error, .has-succes to the Select2 dropdown
        // (was #select2-drop in Select2 v3.x, in Select2 v4 can be selected via
        // body > .select2-container) if _any_ of the opened Select2's parents
        // has one of these forementioned classes (YUCK! ;-))
        $(".select2, .select2-multiple, .select2-ajax", context).on("select2:open", function() {
            if ($(this).parents("[class*='has-']").length) {
                var classNames = $(this).parents("[class*='has-']")[0].className.split(/\s+/);

                for (var i = 0; i < classNames.length; ++i) {
                    if (classNames[i].match("has-")) {
                        $("body > .select2-container").addClass(classNames[i]);
                    }
                }
            }
        });
    }

    var handleFormSubmit = function(context){
        $('form', context).each(function(idx, obj){
            $(obj).on('submit', function(){
                App.blockUI({
                    target: obj,
                    animate: true
                });
            });
        })
    }

    var handleEnabledDependent = function(context){
        $('[data-enabled-dependent]', context).each(function(idx, obj){
            if($('#' + $(obj).data('enabled-dependent')).hasClass('make-switch')){
                $('#' + $(obj).data('enabled-dependent')).on('switchChange.bootstrapSwitch', function(event, state){
                    if($(obj).data('enabled-dependent-effect') == 'disabled'){
                        if(event.target.checked) {
                            $(obj).removeAttr('disabled');
                        }else{
                            $(obj).prop('disabled', true);
                        }
                    }else{
                        if(event.target.checked) {
                            $(obj).show();
                        }else{
                            $(obj).hide();
                        }
                    }
                });

                $('#' + $(obj).data('enabled-dependent')).trigger('switchChange.bootstrapSwitch');
            }else{
                $('#' + $(obj).data('enabled-dependent')).on('change', function(){
                    if($(obj).data('enabled-dependent-effect') == 'disabled'){
                        if(this.checked) {
                            $(obj).removeAttr('disabled');
                        }else{
                            $(obj).prop('disabled', true);
                        }
                    }else{
                        if(event.target.checked) {
                            $(obj).show();
                        }else{
                            $(obj).hide();
                        }
                    }
                });

                $('#' + $(obj).data('enabled-dependent')).change();
            }
        })
    }

    var handleSelectDependent = function(context){
        $('[data-select_dependent]', context).each(function(idx, obj){
            $($(obj).data('select_dependent')).on('change', function(){
                if($(this).val() == $(obj).data('select_dependent_value')){
                    $(obj).show();
                }else if($(this).val() != $(obj).data('select_dependent_not_value')) {
                    $(obj).show();
                }else{
                    $(obj).hide();
                }
            });

            $($(obj).data('select_dependent')).change();
        })
    }

    var handleDateAndTime = function(context){
        //init datepickers
        $('.date-picker', context).datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        //init datetimepickers
        //Remove last seconds
        $(".datetime-picker", context).each(function(idx, obj){
            if($(obj).val() != ''){
                if($(obj).val().split(':').length >= 3){
                    $(obj).val($(obj).val().trim().slice(0, -3));
                }
            }
        });

        $(".form_datetime", context).each(function(idx, obj){
            $(obj).datetimepicker({
                isRTL: App.isRTL(),
                autoclose: true,
                todayBtn: true,
                pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
                minuteStep: 15,
                format: 'yyyy-mm-dd hh:ii',
                showClear: true
            });
        });
    }

    var handleMaxLength = function(context){
        //init maxlength handler
        $('.maxlength-handler', context).maxlength({
            limitReachedClass: "label label-danger",
            alwaysShow: true,
            threshold: 5
        });
    }

    var slugOptions = {
        'translitarate': true,
        'uppercase': false,
        "lowercase": true,
        "divider": '-'
    };

    var handleSlugs = function(context){
        if (!String.prototype.seoURL) {
            return;
        }

        $('[data-slug_source]', context).each(function(idx, obj){
            $($(obj).data('slug_source'), context).on('keyup', function(e){
                $(obj).val(e.target.value.seoURL(slugOptions));
            });
        });
    }

    var handleInputMask = function(context){
        $('input[data-inputmask]', context).inputmask();
    }

    var handleFilesUpload = function (context) {
        $(document).bind('drop dragover', function (e) {
            e.preventDefault();
        });

        var $refreshDropzone = function($uploadedZone){
            var $obj = $uploadedZone.parents('.images-upload');
            if($uploadedZone.find('.uploaded-image').length < $obj.data('limit')){
                $obj.find('.dropzone').show();
            }else{
                $obj.find('.dropzone').hide();
            }
        }

        var $uploadedProcess = function($uploaded, $uploadedZone){
            $uploaded.on('click', function(e){
                e.preventDefault();
                $(this).parent().remove();

                $refreshDropzone($uploadedZone);
            });
        }

        $('.images-upload', context).each(function(idx, obj){
            $(obj).on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            })
                .on('dragover dragenter', function() {
                    $(obj).addClass('is-dragover');
                })
                .on('dragleave dragend drop', function() {
                    $(obj).removeClass('is-dragover');
                });

            // Initialize the jQuery File Upload widget:
            var $uploadedZone = $(obj).find('.files');
            var $limit = $(obj).data('limit');
            var $caption = $(obj).data('caption');

            $refreshDropzone($uploadedZone);

            $(obj).fileupload({
                url: $(obj).data('upload_url'),
                dataType: 'json',
                disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
                maxFileSize: global_vars.max_upload_size,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                dropZone: $(obj).find('.dropzone'),
                add: function(e, data){
                    if(data.originalFiles.length <= $limit && $uploadedZone.find('.uploaded-image').length < $limit){
                        $.each(data.files, function (index, file) {
                            if (file.type.match('image.*')) {
                                data.context = $('<div class="col-md-3"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style=""></div></div></div>');
                                data.context.appendTo($uploadedZone);

                                if($(obj).data('limit') == 1){
                                    $(obj).find('.dropzone').hide();
                                }
                            }else{
                                //data.files.splice(index,1);
                            }
                        });

                        data.submit();
                    }else{
                        //KommercioApp.errorPopup('You can only upload 1 file.');
                    }
                },
                progress: function(e, data){
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    data.context.find('.progress-bar').prop('aria-valuenow', progress).css(
                        'width',
                        progress + '%'
                    );
                },
                done: function(e, data){
                    $.each(data.result.files, function (index, file) {
                        var $uploadedImage = '<div class="col-md-3 uploaded-image text-center"><img class="img-responsive" src="'+global_vars.base_path+'/'+global_vars.images_path+'/backend_thumbnail/'+file.path+'" />';
                        if($caption == 1){
                            $uploadedImage += '<input name="'+$(obj).data('name')+'_caption[]" type="text" placeholder="Caption" class="form-control input-sm" />';
                        }
                        $uploadedImage += '<input name="'+$(obj).data('name')+'[]" type="hidden" value="'+file.id+'" /><a href="#" class="uploaded-image-remove"><i class="fa fa-remove"></i></a></div>';
                        $uploadedImage = $($uploadedImage);

                        $uploadedProcess($uploadedImage.find('.uploaded-image-remove'), $uploadedZone);

                        data.context.replaceWith($uploadedImage);

                        $uploadedZone.sortable('reload');
                    });
                }
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');

            // Enable iframe cross-domain access via redirect option:
            $(obj).fileupload(
                'option',
                'redirect',
                window.location.href.replace(
                    /\/[^\/]*$/,
                    '/cors/result.html?%s'
                )
            );

            $uploadedZone.sortable({
                placeholder: '<div class="col-md-3 uploaded-image"></div>'
            });

            $refreshDropzone($uploadedZone);
            $uploadedProcess($uploadedZone.find('.uploaded-image-remove'), $uploadedZone);
        });
    };

    var handleCurrencyDependent = function(context)
    {
        $('[data-currency_dependent]', context).each(function(idx, obj){
            $($(obj).data('currency_dependent')).on('change', function(){
                var currency = global_vars.default_currency;
                if(String($(this).val()) != ''){
                    currency = $(this).val();
                }

                if($(obj).data('number_type') == 'amount'){
                    $(obj).parent().find('.input-group-addon').text(global_vars.currencies[currency].symbol);
                }
            });

            $($(obj).data('currency_dependent')).change();
        });

        $('[data-number_type_dependent]', context).each(function(idx, obj){
            $($(obj).data('number_type_dependent')).on('change', function(){
                $(obj).removeData('number_type');
                $(obj).attr('data-number_type', $(this).val());

                if($(this).val() == 'percent'){
                    $(obj).parent().find('.input-group-addon').text('%');
                }else{
                    $($(obj).data('currency_dependent')).change();
                }
            });

            $($(obj).data('number_type_dependent')).change();
        });
    }

    var handleMultiselect = function(context)
    {
        $('.multiselect', context).each(function(idx, obj){
            $(obj).multiSelect({
                selectableOptgroup: true
            });
        });
    }

    var handleTabChange = function(context)
    {
        var savedId;
        var tabContent;

        $('body').on('shown.bs.tab', function(e){
            savedId = e.target.hash.substr(1);
            tabContent = $('#'+savedId);
            tabContent.removeAttr('id');

            $.bbq.pushState(e.target.hash);

            tabContent.attr('id', savedId);
        });

        $(window).bind('hashchange', function(e){
            $('a[href="#' + e.fragment + '"]').click();
        });
    }

    var handleTypeahead = function(context)
    {
        $('[data-typeahead_remote]', context).each(function(idx, obj){
            var results = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.whitespace($(obj).data('typeahead_label')),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: $(obj).data('typeahead_remote') + '?query=%QUERY',
                    wildcard: '%QUERY',
                    transform: function(response){
                        return response.data;
                    }
                }
            });

            $(obj).typeahead(
                {
                    minLength: 2,
                    hint: false
                },
                {
                name: 'typeahead-' + idx,
                display: $(obj).data('typeahead_display'),
                source: results.ttAdapter(),
                templates: {
                    pending: '<div>Loading suggestion...</div>',
                    notFound: '<div>No suggestion found...</div>',
                    suggestion: Handlebars.compile('<div>{{'+$(obj).data('typeahead_label')+'}}</div>')
                }
            });
        });
    }

    var handleAddressOptions = function(context)
    {
        $('.address-options-group', context).each(function(idx, obj){
            $(obj).find('.country-select').on('change', function(e){
                $(this).trigger('address.change');

                $.ajax(global_vars.base_path + '/address/state/options', {
                    'data' : 'parent=' + $(e.target).val() + '&active_only=0',
                    'dataType' : 'json',
                    'success' : function(data){
                        var $options = selectHelper.convertToOptions(data, $(obj).find('.state-select').data('first_option'));

                        $(obj).find('.state-select').html($options);
                        $(obj).find('.state-select').trigger('change', true);
                        handleSelects($(obj));

                        delete $options;

                        if(data.length <= 1){
                            $(obj).find('.state-select').closest('.col-md-6').hide();
                        }else{
                            $(obj).find('.state-select').closest('.col-md-6').show();
                        }
                    }
                });
            });

            $(obj).find('.state-select').on('change', function(e, isChain){
                if(!isChain){
                    $(this).trigger('address.change');
                }

                $.ajax(global_vars.base_path + '/address/city/options', {
                    'data' : 'parent=' + $(e.target).val() + '&active_only=0',
                    'dataType' : 'json',
                    'success' : function(data){
                        var $options = selectHelper.convertToOptions(data, $(obj).find('.city-select').data('first_option'));

                        $(obj).find('.city-select').html($options);
                        $(obj).find('.city-select').trigger('change', true);
                        handleSelects($(obj));

                        delete $options;

                        if(data.length <= 1){
                            $(obj).find('.city-select').closest('.col-md-6').hide();
                        }else{
                            $(obj).find('.city-select').closest('.col-md-6').show();
                        }
                    }
                });
            });

            $(obj).find('.city-select').on('change', function(e, isChain){
                if(!isChain){
                    $(this).trigger('address.change');
                }

                $.ajax(global_vars.base_path + '/address/district/options', {
                    'data' : 'parent=' + $(e.target).val() + '&active_only=0',
                    'dataType' : 'json',
                    'success' : function(data){
                        var $options = selectHelper.convertToOptions(data, $(obj).find('.district-select').data('first_option'));

                        $(obj).find('.district-select').html($options);

                        $(obj).find('.district-select').trigger('change', true);
                        handleSelects($(obj));

                        delete $options;

                        if(data.length <= 1){
                            $(obj).find('.district-select').closest('.col-md-6').hide();
                        }else{
                            $(obj).find('.district-select').closest('.col-md-6').show();
                        }
                    }
                });
            });

            $(obj).find('.district-select').on('change', function(e, isChain){
                if(!isChain){
                    $(this).trigger('address.change');
                }

                $.ajax(global_vars.base_path + '/address/area/options', {
                    'data' : 'parent=' + $(e.target).val() + '&active_only=0',
                    'dataType' : 'json',
                    'success' : function(data){
                        var $options = selectHelper.convertToOptions(data, $(obj).find('.area-select').data('first_option'));

                        $(obj).find('.area-select').html($options);
                        $(obj).find('.area-select').trigger('change', true);
                        handleSelects($(obj));

                        delete $options;

                        if(data.length <= 1){
                            $(obj).find('.area-select').closest('.col-md-6').hide();
                        }else{
                            $(obj).find('.area-select').closest('.col-md-6').show();
                        }
                    }
                });
            });

            $(obj).find('.area-select').on('change', function(e, isChain) {
                if(!isChain){
                    $(this).trigger('address.change');
                }
            });

            $(obj).find('select').each(function(idy, objy){
                if($(objy).find('option').length <= 1){
                    $(objy).closest('.col-md-6').hide();
                }
            });
        });
    }

    var handleAjaxModal = function(context)
    {
        $('.modal-ajax', context).on('click', function(e){
            e.preventDefault();

            var $modal = $(this).data('modal_id') == null?'#ajax_modal':$(this).data('modal_id');

            $.ajax($(this).attr('href'), {
                success: function(data){
                    var $loadedData = $(data);
                    $($modal).find('.modal-content').html($loadedData);

                    formBehaviors.init($loadedData);
                    App.initAjax();

                    $($modal).modal('show');
                }
            });
        });
    }

    var handleExpandedDetail = function(context)
    {
        $('.expanded-detail', context).each(function(idx, obj){
            var expandToggle = $('<a href="#" style="font-size: 12px;">Show Detail</a>');
            $(obj).after(expandToggle);
            expandToggle.wrap('<div />');

            $(obj).addClass('note note-info');
            $(obj).hide();

            $(expandToggle).click(function(e){
                e.preventDefault();
                $(obj).toggle(0, function(){
                    if($(this).is(':visible')){
                        expandToggle.text('Hide Detail');
                    }else{
                        expandToggle.text('Show Detail');
                    }
                });
            });
        });
    }

    return {
        init: function(context){
            if(typeof context === 'undefined'){
                context = document;
            }

            handleSummernote(context);
            handleBtnLinks(context);
            handleSelects(context);
            handleSlugs(context);
            handleFormSubmit(context);
            handleFilesUpload(context);
            handleDateAndTime(context);
            handleMaxLength(context);
            handleInputMask(context);
            handleCurrencyDependent(context);
            handleSelectDependent(context);
            handleMultiselect(context);
            handleEnabledDependent(context);
            handleTabChange(context);
            handleAddressOptions(context);
            handleTypeahead(context);
            handleAjaxModal(context);
            handleExpandedDetail(context);
        },
        initComponents: function(context){
            handleInputMask(context);
        },
        reInitInputMask: function(context){
            //Re-init inputmask on #line-items-table level so auto remove mask will work
            $('input[data-inputmask]', context).inputmask('remove');
        }
    }
}();

var formHelper = {
    convertNumber: function(n, thousand_separator, decimal_separator){
        if(typeof thousand_separator === 'undefined'){
            thousand_separator = ',';
        }

        if(typeof decimal_separator === 'undefined'){
            decimal_separator = '.';
        }

        var parts=n.toString().split(decimal_separator);
        return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator) + (parts[1] ? decimal_separator + parts[1] : "");
    },
    roundNumber: function(value, places, rounding){
        if(typeof places === 'undefined'){
            places = global_vars.line_item_total_precision;
        }

        if(typeof rounding === 'undefined'){
            rounding = 'default';
        }

        var multiplier = Math.pow(10, places);

        if(rounding == 'floor'){
            return (Math.floor(value * multiplier) / multiplier);
        }else if(rounding == 'ceil') {
            return (Math.ceil(value * multiplier) / multiplier);
        }else{
            return (Math.round(value * multiplier) / multiplier);
        }
    },
    calculateRounding: function(before, after, round){
        var calculated = after - before;

        if(typeof round === 'undefined'){
            round = true;
        }

        if(round){
            calculated = formHelper.roundNumber(calculated);
        }

        return calculated;
    },
    convertDotToSquareBracket: function(name){
        var parts = String(name).split('.');
        var returnText = '';

        for(var i in parts){
            if(i != 0){
                returnText += '[' + parts[i] + ']';
            }else{
                returnText += parts[i];
            }
        }

        return returnText;
    },
    addFieldError: function(vars){
        if(typeof vars.context === 'undefined'){
            vars.context = document;
        }

        if(typeof vars.highlightField === 'undefined'){
            vars.highlightField = true;
        }

        if(!$(vars.messagesWrapper, vars.context).length){
            $('.portlet-body', vars.context).prepend('<div id="'+vars.messagesWrapper.replace('#', '')+'" class="alert alert-danger"></div>');
        }

        $(vars.messagesWrapper, vars.context).append('<div>' + vars.message + '</div>');

        if(vars.highlightField){
            if(typeof vars.highlightParentPrefix !== 'undefined'){
                $('[name="'+vars.name+'"]', vars.context).parents(vars.highlightParentPrefix + '-default').removeClass(vars.highlightParentPrefix+'-default').addClass(vars.highlightParentPrefix+'-warning');
            }

            $('[name="'+vars.name+'"]', vars.context).parents('.form-group').addClass('has-error');

            var $errorBlock = '<span class="help-block help-error">' + vars.message + '</span>';

            if($('[name="'+vars.name+'"]', vars.context).parent().hasClass('input-group')){
                $('[name="'+vars.name+'"]', vars.context).parent().after($errorBlock);
            }else{
                $('[name="'+vars.name+'"]', vars.context).after($errorBlock);
            }
        }
    },
    clearFormError: function(vars){
        if(typeof vars.highlightParentPrefix !== 'undefined'){
            $(vars.wrapper).find('.' + vars.highlightParentPrefix + '-warning').removeClass(vars.highlightParentPrefix + '-warning').addClass(vars.highlightParentPrefix + '-default');
        }

        $(vars.wrapper).find('.has-error').removeClass('has-error');
        $(vars.wrapper).find('.help-error').remove();

        $(vars.wrapper).find(vars.messagesWrapper).remove();
    }
};

var selectHelper = {
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
};

jQuery(document).ready(function() {
    formBehaviors.init();
});