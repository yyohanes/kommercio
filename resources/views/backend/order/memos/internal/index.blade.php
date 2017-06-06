@foreach($internalMemos as $internalMemo)
    <tr>
        <td>{!! nl2br($internalMemo->body) !!}</td>
        <td>{{ $internalMemo->getData('author_name') }}</td>
        <td><span class="badge badge-default">{{ $internalMemo->created_at->format('d-m-Y H:i') }}</span></td>
    </tr>
@endforeach