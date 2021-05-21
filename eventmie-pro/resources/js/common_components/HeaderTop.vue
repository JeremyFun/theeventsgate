<template>
    <div class="row" style="margin-left: 0!important; margin-right: 0!important;">
        <div
            v-match-heights="{
                el: ['h5.sub-title'],  // Array of selectors to fix
            }"
            v-for="(event, index) in events"
            :key="index"
        >
            <div class="meta-wrapper" v-if="index === 0">
                            <span class="icon banner-icon" style="color: red;">
     <i class="fas fa-calendar text-danger mr-2"></i>
     {{ trans('em.upcoming_events') }}: <span style="color: gray;">{{
                                    event.title
                                }} '{{
                                    changeDateFormat(convert_date_to_local(index === 0 ? event.end_date : ""), "YYYY-MM-DD")
                                }} {{ changeTimeFormat(event.start_time)}} - {{ changeTimeFormat(event.end_time)}}' on {{ index === 0 ? event.city : "" }}</span>
     </span>
            </div>
        </div>
    </div>
</template>

<script>

import mixinsFilters from '../mixins.js';

export default {

    props: ['events', 'currency'],

    mixins: [
        mixinsFilters
    ],

    data() {
        return {
            not_found: false,
        }
    },

    // methods:{
    //     // check free tickets of events
    //     checkFreeTickets(event_tickets = []){
    //         let free = false;
    //         event_tickets.forEach(function(value, key) {
    //             if(parseFloat(value.price) <= parseFloat(0))
    //             {
    //                 free = true;
    //             }
    //         });
    //         return free;
    //     },
    //
    //
    //     // return route with event slug
    //     eventSlug: function eventSlug(slug) {
    //         return route('eventmie.events_show', [slug]);
    //     }
    //
    //
    // },

    watch: {
        events: function () {
            this.not_found = false;
            if (this.events.length <= 0)
                this.not_found = true;
        },
    },

}
</script>
