{{-- Common between Admin, Customer & Organiser --}}
<li class="guest__li">
    @php
        $data  = notifications();
    @endphp

    <a id="navbarDropdown" class="dropdown-toggle active text-primary" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" v-pre>
        <i class="fas fa-bell"> </i>
        <span class="badge bg-red">{{$data['total_notify']}}</span>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        @if(!empty($data['notifications']))
            @foreach ($data['notifications'] as $notification)
            <li class="guest__li">
                <a class="dropdown-item text-primary" href="{{route('eventmie.notify_read', [$notification->n_type])}}">
                    {{ $notification->total }} @lang('eventmie-pro::em.new') {{ ucfirst($notification->n_type) }}
                </a>
            </li>
            @endforeach
        @else
        <li>
            <a class="dropdown-item text-primary" > @lang('eventmie-pro::em.no_notifications')</a>
        </li>
        @endif
    </ul>
</li>


<li class="guest__li">
    <a id="navbarDropdown" class="dropdown-toggle active text-primary" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" v-pre>
        @if(Auth::user()->hasRole('customer'))
        <i class="fas fa-user-circle"></i>
        @elseif(Auth::user()->hasRole('organiser'))
        <i class="fas fa-person-booth"></i>
        @else
        <i class="fas fa-user-shield"></i>
        @endif

        {{ Auth::user()->name }} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu multi-level">

        {{-- Customer --}}
        @if(Auth::user()->hasRole('customer'))
        <li class="guest__li">
            <a class="dropdown-item text-primary" href="{{ route('eventmie.profile') }}"><i class="fas fa-id-card"></i> @lang('eventmie-pro::em.profile')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.mybookings_index') }}"><i class="fas fa-money-check-alt"></i> @lang('eventmie-pro::em.mybookings')</a>
        </li>
        @endif

        {{-- Organiser --}}
        @if(Auth::user()->hasRole('organiser'))
        <li class="guest__li">
            <a class="dropdown-item text-primary" href="{{ route('eventmie.profile') }}"><i class="fas fa-id-card"></i> @lang('eventmie-pro::em.profile')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.organizer_dashboard') }}"><i class="fas fa-tachometer-alt"></i> @lang('eventmie-pro::em.dashboard')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.myevents_index') }}"><i class="fas fa-calendar-alt"></i> @lang('eventmie-pro::em.manage_events')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.obookings_index') }}"><i class="fas fa-money-check-alt"></i> @lang('eventmie-pro::em.manage_bookings')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.event_earning_index') }}"><i class="fas fa-wallet"></i> @lang('eventmie-pro::em.manage_earning')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.tags_form') }}"><i class="fas fa-user-tag"></i> @lang('eventmie-pro::em.manage_tags')</a>
        </li>
        @endif

        {{-- Admin --}}
        @if(Auth::user()->hasRole('admin'))
        <li class="guest__li">
            <a class="dropdown-item text-primary" href="{{ eventmie_url().'/'.config('eventmie.route.admin_prefix') }}">
            <i class="fas fa-tachometer-alt"></i>  @lang('eventmie-pro::em.admin_panel')</a>
        </li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('eventmie.profile') }}"><i class="fas fa-id-card"></i> @lang('eventmie-pro::em.profile')</a>
        </li>
        @endif

        <li class="guest__li">
            <a class="dropdown-item text-primary" href="{{ route('eventmie.logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> @lang('eventmie-pro::em.logout')
            </a>
            <form id="logout-form" action="{{ route('eventmie.logout') }}" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </li>
    </ul>
</li>
