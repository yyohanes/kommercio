@if($country_id)
<div class="col-md-3">
    <label>State</label>
    {!! Form::select('states[]', $stateOptions, old('states', isset($tax)?$tax->states->pluck('id')->all():null), ['class' => 'form-control', 'size' => 10, 'multiple' => TRUE]) !!}
</div>

<div class="col-md-3">
    <label>City</label>
    {!! Form::select('cities[]', $cityOptions, old('cities', isset($tax)?$tax->cities->pluck('id')->all():null), ['class' => 'form-control', 'size' => 10, 'multiple' => TRUE]) !!}
</div>

<div class="col-md-3">
    <label>District</label>
    {!! Form::select('districts[]', $districtOptions, old('districts', isset($tax)?$tax->districts->pluck('id')->all():null), ['class' => 'form-control', 'size' => 10, 'multiple' => TRUE]) !!}
</div>

<div class="col-md-3">
    <label>Area</label>
    {!! Form::select('areas[]', $areaOptions, old('areas', isset($tax)?$tax->areas->pluck('id')->all():null), ['class' => 'form-control', 'size' => 10, 'multiple' => TRUE]) !!}
</div>

<div class="col-md-12">
    <div class="help-block">If no option is selected, it will considered All.</div>
</div>
@endif