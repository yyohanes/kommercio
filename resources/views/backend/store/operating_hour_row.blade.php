@php
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
@endphp
<div class="operatingTime-row portlet light bordered">
    <div class="portlet-title">
        <div class="caption"><span class="caption-subject">Operating Schedule</span></div>
        <div class="tools">
            <a class="collapse"></a>
            <a class="remove"></a>
        </div>
    </div>
    <div id="openingTimes_{{ $idx }}" class="portlet-body">
        <div class="row">
            <div class="col-sm-2">
                @include('backend.master.form.fields.checkbox', [
                    'name' => 'openingTimes['.$idx.'][open]',
                    'label' => null,
                    'key' => 'openingTimes.'.$idx.'.open',
                    'value' => 1,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'openingTimes['.$idx.'][open]',
                        'data-on-color' => 'warning',
                        'data-on-text' => 'Open',
                        'data-off-text' => 'Closed',
                    ],
                    'checked' => old('openingTimes.'.$idx.'.open', isset($openingTime['open'])?$openingTime['open']:TRUE),
                ])
            </div>

            <div class="col-sm-2 col-xs-6">
                <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </span>
                    {!! Form::text('openingTimes['.$idx.'][time_from]', null, ['class' => 'form-control time-picker', 'placeholder' => 'From']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-xs-6">
                <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </span>
                    {!! Form::text('openingTimes['.$idx.'][time_to]', null, ['class' => 'form-control time-picker', 'placeholder' => 'Until']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-xs-6">
                <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar-o"></i>
                                        </span>
                    {!! Form::text('openingTimes['.$idx.'][date_from]', null, ['class' => 'form-control date-picker', 'data-date-format' => 'yyyy-mm-dd', 'placeholder' => 'From']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-xs-6">
                <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar-o"></i>
                                        </span>
                    {!! Form::text('openingTimes['.$idx.'][date_to]', null, ['class' => 'form-control date-picker', 'data-date-format' => 'yyyy-mm-dd', 'placeholder' => 'Until']) !!}
                </div>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-md-3">
                @include('backend.master.form.fields.checkbox', [
                    'name' => 'openingTimes['.$idx.'][everyday]',
                    'label' => null,
                    'key' => 'openingTimes.'.$idx.'.everyday',
                    'value' => 1,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'openingTimes-'.$idx.'-everyday',
                        'data-on-color' => 'warning',
                        'data-on-text' => 'Everyday',
                        'data-off-text' => 'Not Everyday',
                    ],
                    'checked' => old('openingTimes.'.$idx.'.everyday', $openingTime['isEveryday']),
                ])
            </div>
            <div class="col-xs-9">
                <div data-enabled-dependent="openingTimes-{{ $idx }}-everyday" data-enabled-dependent-negate="1">
                    <div class="checkbox-list">
                        @foreach($days as $day)
                            <label class="checkbox-inline">
                                {!! Form::checkbox('openingTimes['.$idx.']['.$day.']', 1, !empty($openingTime[$day])) !!}
                                {{ ucfirst($day) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! Form::hidden('openingTimes['.$idx.'][id]', isset($openingTime['id'])?$openingTime['id']:null) !!}
</div>