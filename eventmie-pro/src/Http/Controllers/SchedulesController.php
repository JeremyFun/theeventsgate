<?php

namespace Classiebit\Eventmie\Http\Controllers;
use App\Http\Controllers\Controller; 
use Facades\Classiebit\Eventmie\Eventmie;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Auth;


use Classiebit\Eventmie\Models\Event;
use Classiebit\Eventmie\Models\Schedule;


class SchedulesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // language change
        $this->middleware('common');
    
        $this->schedule     = new Schedule;
        $this->event        = new Event;
        $this->organiser_id = null;   
    }

    // get event's schedules while creating event
    // GET SHEDULE OF LOGING USER AND BASE ON EVENT ID 
    public function schedules(Request $request)
    {   
        // if logged in user is admin
        $this->is_admin($request);

        $request->validate([
            'event_id'          => 'required',
        ]);

        $event    = $this->event->get_user_event($request->event_id, $this->organiser_id);

        if(empty($event))
        {
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        }

        $params      = [
            'event_id'  => $request->event_id,
            'user_id'   => $this->organiser_id,
        ];

        
        $schedules   = $this->schedule->get_schedule($params);
    
        if(empty($schedules))
        {
            return error(__('eventmie-pro::em.schedule').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        }

        return response()->json(['status' => true, 'schedules' => $schedules]);
    }


    // get event's schedules while showing event
    // GET SCHEDULE  CLIENT  WTIHOUT LOGIN USER ID SHEDULE BASE ON EVENT ID
    public function event_schedule(Request $request)
    {   
        $request->validate([
            'event_id'          => 'required',
        ]);

        if(empty($request->event_id))
        {
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        }

        $params      = [
            'event_id'  => $request->event_id,
        ];

        $schedules   = $this->schedule->get_event_schedule($params);
        
        if(empty($schedules))
        {
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        }

        return response()->json(['status' => true, 'schedules' => $schedules]);
    }

    protected function is_admin(Request $request)
    {
        // if login user is Organiser then 
        // organiser id = Auth::id();
        $this->organiser_id = Auth::id();

        // if admin is creating event
        // then user Auth::id() as $organiser_id
        // and organiser id will be the id selected from Vue dropdown
        if(Auth::user()->hasRole('admin'))
        {
            $request->validate([
                'organiser_id'       => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            ]);

            $this->organiser_id = $request->organiser_id;
        }
    }
}