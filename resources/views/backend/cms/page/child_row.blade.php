@foreach($page->children as $childPage)
    <tr>
        <td>{{ str_pad($childPage->name, $level + strlen(trim($childPage->name)), '-', STR_PAD_LEFT) }}</td>
        <td>{{ $childPage->slug }}</td>
        <td><i class="fa fa-{{ $childPage->active?'check text-success':'remove text-danger' }}"></i></td>
        <td class="text-center">
            {!! Form::open(['route' => ['backend.cms.page.delete', 'id' => $childPage->id]]) !!}
            <div class="btn-group btn-group-sm">
                @can('access', ['edit_page'])
                <a class="btn btn-default" href="{{ route('backend.cms.page.edit', ['id' => $childPage->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                @endcan
                @can('access', ['delete_page'])
                <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                @endcan
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    @include('backend.cms.page.child_row', ['page' => $childPage, 'level' => $level + 1])
@endforeach