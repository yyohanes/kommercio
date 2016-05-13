{!! Form::open(['route' => 'backend.file.upload', 'files' => TRUE]) !!}
    {!! Form::file('test_name[]', ['multiple']) !!}
    {!! Form::submit('Submit') !!}
{!! Form::close() !!}