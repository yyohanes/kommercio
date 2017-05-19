@extends('backend.master.form_template')

@section('content')
    <div class="col-md-12">
        @include('backend.utility.export.messages')

        <div id="export-wrapper">
            @yield('form')
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/export_page.js') }}" type="text/javascript"></script>

    @if($runUrl)
        <script>
            jQuery(document).ready(function() {
                ExportPage.init('{!! $runUrl !!}', $('#export-wrapper'));
            });
        </script>
    @endif

    @if(Request::input('success'))
        <script>
          setTimeout(function(){
            $('#export-wrapper').append('<iframe width="1" height="1" frameborder="0" src="{{ route('backend.utility.export.download', ['batch_id' => Request::input('batch_id')]) }}"></iframe>');
          }, 250);
        </script>
    @endif
@endsection