{{-- Load third party plugins in seperate file (node modules) --}}
<script type="text/javascript" src="{{ eventmie_asset('js/manifest.js') }}"></script>

{{-- localization --}}
<script type="text/javascript" src="{{ route('eventmie.eventmie_lang') }}"></script>

{{-- VueJs Global Constants --}}
<script type="text/javascript">
    const my_lang = {!! json_encode(session('my_lang', 'en')) !!};
    const date_format = {
        vue_date_format: '{!! format_js_date() !!}',
        vue_time_format: '{!! format_js_time() !!}'
    };
</script>

{{-- Javascript Global Listener --}}
<script type="text/javascript">
/**
 * Header menu onscroll 
 */
var lastScrollTop = 0;
function handleScroll() {
    let el = document.getElementById('navbar_vue');
    let st = window.pageYOffset || document.documentElement.scrollTop;
    
    lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling
    if(window.scrollY > 1) {
        el.classList.add('menu-onscroll');
    } else {
        el.classList.remove('menu-onscroll');
      
        if(el.classList.contains('is-active')) {
            el.classList.add('is-mobile');
        }
    }
};

function scrollListener( obj, type, fn ) {
    if ( obj.attachEvent ) {
        obj['e'+type+fn] = fn;
        obj[type+fn] = function(){obj['e'+type+fn]( window.event );};
        obj.attachEvent( 'on'+type, obj[type+fn] );
    } else {
        obj.addEventListener( type, fn, false );
    }
}

scrollListener(window, 'scroll', function(e) {
    handleScroll();
});
</script>