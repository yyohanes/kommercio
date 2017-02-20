<div class="form-group">
    <div class="col-md-12">
        <label>{{ $productConfiguration->name }}</label>
        {!! Form::textarea('line_items['.$key.'][product_configuration]['.$productConfiguration->id.']', null, ['class' => 'form-control', 'rows' => 2, 'maxlength' => ($productConfiguration->pivot->maximum>0?$productConfiguration->pivot->maximum:null)]) !!}
    </div>
</div>