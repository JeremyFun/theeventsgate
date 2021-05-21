{{-- Admin --}}
@if(Auth::user()->hasRole('admin'))
<li class="guest__li">
    <a class="lgx-scroll lgx-scroll-authorized" href="{{ route('eventmie.ticket_scan') }}">
    <i class="fas fa-qrcode"></i> @lang('eventmie-pro::em.scan_ticket')</a>
</li>
<li>
    <a class="lgx-scroll lgx-scroll-authorized" href="{{ route('eventmie.myevents_form') }}">
    <i class="fas fa-calendar-plus"></i> @lang('eventmie-pro::em.create_event')</a>
</li>
@endif

{{-- Organiser --}}
@if(Auth::user()->hasRole('organiser') && setting('multi-vendor.multi_vendor'))
<li class="guest__li">
    <a class="lgx-scroll lgx-scroll-authorized" href="{{ route('eventmie.ticket_scan') }}">
    <i class="fas fa-qrcode"></i> @lang('eventmie-pro::em.scan_ticket')</a>
</li>
<li>
    <a class="lgx-scroll lgx-scroll-authorized" href="{{ route('eventmie.myevents_form') }}">
    <i class="fas fa-calendar-plus"></i> @lang('eventmie-pro::em.create_event')</a>
</li>
@endif

{{-- Customer --}}
@if(Auth::user()->hasRole('customer'))
<li class="guest__li">
    <a class="lgx-scroll lgx-scroll-authorized" href="{{ route('eventmie.mybookings_index') }}">
    <i class="fas fa-money-check-alt"></i> @lang('eventmie-pro::em.mybookings')</a>
</li>
@endif
