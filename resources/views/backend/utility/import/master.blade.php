@extends('backend.master.form_template')

@section('content')
    <div class="col-md-12">
        @include('backend.utility.import.messages')

        <div id="import-wrapper">
            @yield('form')
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/import_page.js') }}" type="text/javascript"></script>

    @if($runUrl)
        <script>
            jQuery(document).ready(function() {
                ImportPage.init('{!! $runUrl !!}', $('#import-wrapper'));
            });
        </script>
    @endif
@endsection