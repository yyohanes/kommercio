<div class="form-group">
    <div class="col-md-12">
        <label>{{ $productConfiguration->name }}</label>
        {!! Form::text('line_items['.$parentKey.'][children]['.$composite->id.']['.$key.'][product_configuration]['.$productConfiguration->id.']', null, ['class' => 'form-control', 'maxlength' => ($productConfiguration->pivot->maximum>0?$productConfiguration->pivot->maximum:null)]) !!}
    </div>
</div>