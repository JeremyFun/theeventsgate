@extends('eventmie::events.show')

@section('javascript')
<script type="text/javascript">
    var google_map_key = {!! json_encode( $google_map_key) !!};
    var stripe_publishable_key = {!! json_encode(env('STRIPE_KEY')) !!};
    var stripe_secret_key = {!! \Auth::id() ? json_encode(\Auth::user()->createSetupIntent()->client_secret) : "null" !!};
    
</script>
<script src="https://cdn.jsdelivr.net/npm/v-mask/dist/v-mask.min.js"></script>
<script type="text/javascript" src="{{ asset('js/events_show_v1.6.js') }}"></script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>

@stop
