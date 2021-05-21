<template>
    <div v-if="booking.is_paid == 1 && booking.status == 1"> 
        <a :href="addToGoogleCalendar" target="_blank" class="lgx-btn lgx-btn-white lgx-btn-sm mb-2" ><i class="fab fa-google"></i> {{ trans('em.add_event_to_google') }}</a>
    </div>                      
</template>


<script>
import mixinsFilters from '../../mixins.js';
export default {
    mixins:[
        mixinsFilters
    ],
    
    props:['booking', 'date_format'],

    computed: {
        addToGoogleCalendar() {
            let start_date = moment(this.convert_date_to_local(this.booking.event_start_date)).format("YYYYMMDDT");
            let start_time = this.convert_time_to_local_real(this.booking.event_start_date, this.booking.event_start_time, "HHmmSS");
            let end_date = moment(this.convert_date_to_local(this.booking.event_end_date)).format("YYYYMMDDT");
            let end_time = this.convert_time_to_local_real(this.booking.event_end_date, this.booking.event_end_time, "HHmmSS");
            
            let google = 'https://www.google.com/calendar/render?action=TEMPLATE';
            google += '&text='+this.booking.event_title;
            google += '&dates='+start_date+start_time+'/'+end_date+end_time;

            if(this.booking.event_excerpt != null)
                google += '&details='+this.booking.event_excerpt;

            google += '&location='+this.booking.event_venue;
            google += '&sprop=website:'+window.location.hostname;
            google += '&sprop=name:'+this.booking.event_slug;

            return google;
        },
    },
    
}
</script>