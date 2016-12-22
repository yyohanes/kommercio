function handleAddressSelector(context)
{
    $('.address-groups', context).each(function(idx, obj){
        var $countrySelect = $('.country-select', obj);
        var $stateSelect = $('.state-select', obj);
        var $citySelect = $('.city-select', obj);
        var $districtSelect = $('.district-select', obj);
        var $areaSelect = $('.area-select', obj);

        $countrySelect.on('change', function(e){
            $.ajax(global_vars.base_path + '/address/state/options?first_option=1', {
                'data' : 'parent=' + $(e.target).val(),
                'dataType' : 'json',
                'success' : function(data){
                    var $options = KommercioFrontend.selectHelper.convertToOptions(data);

                    $stateSelect.html($options);

                    $stateSelect.trigger('change');

                    if(data.length < 1){
                        $(obj).find('.state-select-wrapper').hide();
                    }else{
                        $(obj).find('.state-select-wrapper').show();
                    }

                    delete $options;
                }
            });
        });

        $stateSelect.on('change', function(e){
            $.ajax(global_vars.base_path + '/address/city/options?first_option=1', {
                'data' : 'parent=' + $(e.target).val(),
                'dataType' : 'json',
                'success' : function(data){
                    var $options = KommercioFrontend.selectHelper.convertToOptions(data);

                    $citySelect.html($options);

                    $citySelect.trigger('change');

                    if(data.length < 1){
                        $(obj).find('.city-select-wrapper').hide();
                    }else{
                        $(obj).find('.city-select-wrapper').show();
                    }

                    delete $options;
                }
            });
        });

        $citySelect.on('change', function(e){
            $.ajax(global_vars.base_path + '/address/district/options?first_option=1', {
                'data' : 'parent=' + $(e.target).val(),
                'dataType' : 'json',
                'success' : function(data){
                    var $options = KommercioFrontend.selectHelper.convertToOptions(data);

                    $districtSelect.html($options);

                    $districtSelect.trigger('change');

                    if(data.length < 1){
                        $(obj).find('.district-select-wrapper').hide();
                    }else{
                        $(obj).find('.district-select-wrapper').show();
                    }

                    delete $options;
                }
            });
        });

        $districtSelect.on('change', function(e){
            $.ajax(global_vars.base_path + '/address/area/options?first_option=1', {
                'data' : 'parent=' + $(e.target).val(),
                'dataType' : 'json',
                'success' : function(data){
                    var $options = KommercioFrontend.selectHelper.convertToOptions(data);

                    $areaSelect.html($options);

                    $areaSelect.trigger('change');

                    if(data.length < 1){
                        $(obj).find('.area-select-wrapper').hide();
                    }else{
                        $(obj).find('.area-select-wrapper').show();
                    }

                    delete $options;
                }
            });
        });

      if($countrySelect.find('option').length == 2){
        $countrySelect.find('option').prop('selected', false);
        $countrySelect.find('option:eq(1)').prop('selected', true);

        $countrySelect.trigger('change');
      }

      if($stateSelect.find('option').length < 2){
        $countrySelect.trigger('change');
      }

      if($citySelect.find('option').length < 2){
        $stateSelect.trigger('change');
      }

      if($districtSelect.find('option').length < 2){
        $citySelect.trigger('change');
      }

      if($areaSelect.find('option').length < 2){
        $districtSelect.trigger('change');
      }
    });
}

function removeEmptyP(context)
{
    //Remove empty <p> from tab content
    $('p:empty', context).remove();
}