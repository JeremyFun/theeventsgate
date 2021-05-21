<header>
    <div class="lgx-header">
    <div class="header__top">
    <div  class="lgx-container">
    <div class="row" style="margin-left: 0!important; margin-right: 0!important;">
    <div class="col-7" style="padding-right: 0!important; padding-left: 0!important;">
     @if(!empty($upcomming_events))
     <header-top
                              :events="{{ json_encode($upcomming_events, JSON_HEX_APOS) }}"
                              :currency="{{ json_encode($currency, JSON_HEX_APOS) }}">
     </header-top>
     @endif
     </span>
    </div>
    <div class="col-5 header__topRight" style="display: flex; list-style: none;">
     @guest
                                    @include('eventmie::layouts.guest_header')
                                @else
                                    @include('eventmie::layouts.member_header')
                                @endguest
    </div>
    </div>
    </div>
    </div>
        <div id="navbar_vue" class="lgx-header-position lgx-header-position-white lgx-header-position-fixed">
            <div class="lgx-container" >
                <!-- GDPR -->
                <cookie-law theme="gdpr" button-text="@lang('eventmie-pro::em.accept')">
                    <div slot="message">
                        <gdpr-message></gdpr-message>
                    </div>
                </cookie-law>
                <!-- GDPR -->

                <!-- Vue Alert message -->
                @if ($errors->any())
                    <alert-message :errors="{{ json_encode($errors->all(), JSON_HEX_APOS) }}"></alert-message>
                @endif

                @if (session('status'))
                    <alert-message :message="'{{ session('status') }}'"></alert-message>
                @endif
                <!-- Vue Alert message -->

                <nav class="navbar navbar-default lgx-navbar navbar-expand-lg">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" onclick="document.getElementById('navbar').classList.toggle('in')">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <div class="lgx-logo">
                            <a href="{{ eventmie_url() }}" class="lgx-scroll">
                                <img src="/storage/{{ setting('site.logo') }}" alt="{{ setting('site.site_name') }}"/>
                                <span class="brand-name">{{ setting('site.site_name') }}</span>
                                <span class="brand-slogan">{{ setting('site.site_slogan') }}</span>
                            </a>
                        </div>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav lgx-nav navbar-right">
                            <!-- Authentication Links -->
                            @guest
                                @include('eventmie::layouts.guest_headerDown')
                            @else
                                @include('eventmie::layouts.member_headerDown')
                            @endguest


                            {{-- Common Header --}}
                            {{-- categories menu items --}}
                            @php $categoriesMenu = categoriesMenu() @endphp
                            @if(!empty($categoriesMenu))
                            <li>
                                <a id="navbarDropdown" class="dropdown-toggle active text-primary" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-stream"></i> @lang('eventmie-pro::em.categories') <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu multi-level">
                                    @foreach($categoriesMenu as $val)
                                    <li>
                                        <a class="lgx-scroll text-primary" href="{{route('eventmie.events_index', ['category' => urlencode($val->name)])}}">
                                            {{ $val->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif

                            {{-- additional header menu items --}}
                            @php $headerMenuItems = headerMenu() @endphp
                            @if(!empty($headerMenuItems))
                            <li>
                                <a id="navbarDropdown" class="dropdown-toggle active text-primary" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-th"></i> @lang('eventmie-pro::em.more') <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu multi-level">
                                    @foreach($headerMenuItems as $parentItem)
                                        @if(!empty($parentItem->submenu))
                                        <li class="dropdown-submenu">
                                            <a disabled class="dropdown-toggle disabled" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="{{ $parentItem->icon_class }}"></i> {{ $parentItem->title }} &nbsp;&nbsp;<i class="fas fa-angle-right"></i></a>
                                            <ul class="dropdown-menu">
                                                @foreach($parentItem->submenu as $childItem)
                                                <li>
                                                    <a target="{{ $childItem->target }}" href="{{ $childItem->url }}">
                                                        <i class="{{ $childItem->icon_class }}"></i> {{ $childItem->title }}
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        @else
                                        <li>
                                            <a class="lgx-scroll" target="{{ $parentItem->target }}" href="{{ $parentItem->url }}">
                                                <i class="{{ $parentItem->icon_class }}"></i> {{ $parentItem->title }}
                                            </a>
                                        </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                            @endif

                            <li>
                                <a class="lgx-scroll lgx-btn lgx-btn-sm" href="{{ route('eventmie.events_index') }}"><i class="fas fa-calendar-day"></i> @lang('eventmie-pro::em.browse_events')</a>
                            </li>

                        </ul>
                    </div><!--/.nav-collapse -->
                </nav>
            </div>
            <!-- //.CONTAINER -->
        </div>
    </div>
</header>


@section('javascript')

<script type="text/javascript">
    var google_map_key = {!! json_encode(setting('apps.google_map_key')) !!};
</script>
<script type="text/javascript" src="{{ eventmie_asset('js/welcome_v1.6.js') }}"></script>
@stop
