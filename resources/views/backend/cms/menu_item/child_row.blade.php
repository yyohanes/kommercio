@if($menuItems->count() > 0)
<ol class="dd-list">
    @foreach($menuItems as $menuItem)
        <li class="dd-item dd3-item" data-id="{{ $menuItem->id }}">
            <div class="dd-handle dd3-handle"> </div>
            <div class="dd3-content">
                <table style="width: 80%;">
                    <tr>
                        <td style="width: 70%;">{{ $menuItem->name }} <i class="fa fa-{{ $menuItem->active?'check text-success':'remove text-danger' }}"></i></td>
                        <td class="text-right">
                            {!! Form::open(['route' => ['backend.cms.menu_item.delete', 'id' => $menuItem->id], 'class' => 'form-in-btn-group']) !!}
                            <div class="btn-group btn-group-xs">
                                @can('access', ['edit_menu_item'])
                                <a class="btn btn-default" href="{{ route('backend.cms.menu_item.edit', ['id' => $menuItem->id, 'backUrl' => Request::getRequestUri()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                @endcan
                                @can('access', ['delete_menu_item'])
                                <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                @endcan
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                </table>
            </div>

            @include('backend.cms.menu_item.child_row', ['menuItems' => $menuItem->children])
        </li>
    @endforeach
</ol>
@endif