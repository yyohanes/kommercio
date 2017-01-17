<?php
$placeholder = '';
if($productConfiguration->getData('rules.max', 0) > 0){
    $placeholder = 'Maximum '.$productConfiguration->getData('rules.max').' Characters';
}
?>
<div class="configuration-field-wrapper">
    <label class="configuration-label" for="{{ 'product-'.$product->id.'-'.$productConfiguration->id }}">{{ $productConfiguration->name }}</label>
    <div class="configuration-field">
        {!! Form::text('product_configuration['.$product->id.']['.$productConfiguration->id.']', null, ['maxlength' => ($productConfiguration->getData('rules.max', 0) > 0)?$productConfiguration->getData('rules.max'):null, 'placeholder' => $placeholder, 'class' => 'form-control', 'id' => 'product-'.$product->id.'-'.$productConfiguration->id]) !!}
    </div>
</div>