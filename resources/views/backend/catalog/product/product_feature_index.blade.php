<div class="margin-bottom-10">
    <div class="form-group">
        <label class="control-label col-md-2">Add Features</label>
        <div class="col-md-4">
            {!! Form::select('new_features[]', $featureOptions, null, ['id' => 'new-features-select', 'class' => 'form-control', 'multiple' => TRUE]) !!}
        </div>
        <div class="col-md-1">
            <a class="btn btn-default" id="product-feature-add-btn" href="#">
                <i class="icon-plus"></i> Add
            </a>
        </div>
    </div>
</div>

<div class="table-scrollable">
<table class="table table-hover">
    <thead>
    <tr>
        <th> Feature </th>
        <th> Value </th>
        <th style="width: 25%;"> Custom Value </th>
        <th style="width: 5%;"> </th>
    </tr>
    </thead>
    <tbody>
    @foreach($features as $feature)
        <tr>
            <td> {{ $feature->name }} </td>
            <td> {!! Form::select('features['.$feature->id.']', ['' => '-'] + $feature->getValueOptions(), old('features.'.$feature->id, $product->getProductFeatureValue($feature->id)), ['class' => 'form-control']) !!} </td>
            <td> {!! Form::text('features_custom['.$feature->id.']', null, ['class' => 'form-control']) !!} </td>
            <td style="width: 20%;">
                <a class="feature-remove-btn btn btn-default" href="#" data-feature_id="{{ $feature->id }}"><i class="fa fa-remove"></i></a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>