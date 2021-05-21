@extends('eventmie::layouts.app')

{{-- Page title --}}
@section('title')
    @lang('eventmie-pro::em.manage_events')
@endsection

    
@section('content')
<main>
    <div class="lgx-page-wrapper">
        <section>
            <router-view ></router-view>
        </section> 
    </div>
</main>
@endsection

@section('javascript')
<script>    
    var path           = {!! json_encode($path, JSON_HEX_TAG) !!};
</script>
    
<script type="text/javascript" src="{{ eventmie_asset('js/myevents_v1.6.js') }}"></script>
@stop