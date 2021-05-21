<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\EventsController as BaseEventsController;
use Classiebit\Eventmie\Models\Event;
class EventsController extends BaseEventsController
{
    /**
     * Show single event
     *
     * @return array
     */
    public function show(Event $event, $view = 'vendor.eventmie-pro.events.show', $extra = [])
    {   
        return parent::show($event, $view, $extra);
    }
}
