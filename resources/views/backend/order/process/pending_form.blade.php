<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Place Order</h4>
</div>

<div class="modal-body">
    <div class="form-body">
        <div class="form-group">
            <div class="checkbox-list">
                <label class="checkbox-inline">
                    {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
                </label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer text-center">
    <button type="submit" name="action" value="place_order" class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
</div>