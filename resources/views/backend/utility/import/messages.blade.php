@if($rows->count() > 0)
    <div class="well">
        @foreach($rows as $row)
            <div>
                <code class="{{ $row->status==\Kommercio\Utility\Import\Item::STATUS_SUCCESS?'text-success':'text-danger' }}">{{ $row->name }}: {{ $row->status==\Kommercio\Utility\Import\Item::STATUS_SUCCESS?'Successfully imported.':'Failed. '.$row->notes }}</code>
            </div>
        @endforeach
    </div>
@endif