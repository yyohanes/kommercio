@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE
])

<div class="form-group">
    <label class="control-label col-md-3">Permissions</label>
    <div class="col-md-9">
        @foreach(\Kommercio\Models\Role\Role::getAvailablePermissions() as $permissionGroupName => $permissionGroup)
            <h4 class="block">{{ $permissionGroupName }} <a href="#" class="permissions-check-all">Check All</a> / <a href="#" class="permissions-uncheck-all">Uncheck All</a></h4>
            <div class="row permissions-group">
                @foreach($permissionGroup as $permissionId => $permissionName)
                    <div class="col-sm-3 col-xs-4">
                        <label>{!! Form::checkbox('permissions['.$permissionId.']', 1, old('permissions.'.$permissionId, $role->hasPermission($permissionId))) !!} {{ $permissionName }}</label>
                    </div>
                @endforeach
            </div>
            <hr/>
        @endforeach
    </div>
</div>

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/role_form.js') }}" type="text/javascript"></script>
@stop