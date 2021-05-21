<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\BookingsController as BaseBookingsController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Auth;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BookingsController extends BaseBookingsController
{
    // book tickets
    public function book_tickets(Request $request)
    {
        // check login user role
        $status = $this->is_admin_organiser($request);

        // organiser can't book other organiser event's tikcets but  admin can book any organiser events'tikcets for customer
        if(!$status)
        {
            return response([
                'status'    => false,
                'url'       => route('eventmie.events_index'),
                'message'   => __('eventmie-pro::em.organiser_note_5'),
            ], Response::HTTP_OK);
        }

        // 1. General validation and get selected ticket and event id
        $data = $this->general_validation($request);
        if(!$data['status'])
            return error($data['error'], Response::HTTP_BAD_REQUEST);
            
        // 2. Check availability
        $check_availability = $this->availability_validation($data);
        if(!$check_availability['status'])
            return error($check_availability['error'], Response::HTTP_BAD_REQUEST);

        // 3. TIMING & DATE CHECK 
        $pre_time_booking   =  $this->time_validation($data);    
        if(!$pre_time_booking['status'])
            return error($pre_time_booking['error'], Response::HTTP_BAD_REQUEST);

        $selected_tickets   = $data['selected_tickets'];
        $tickets            = $data['tickets'];

        
        $booking_date = $request->booking_date;

        $params  = [
            'customer_id' => $this->customer_id,
        ];
        // get customer information by customer id    
        $customer   = $this->user->get_customer($params);

        if(empty($customer))
            return error($pre_time_booking['error'], Response::HTTP_BAD_REQUEST);     

        $booking        = [];
        $price          = 0;
        $total_price    = 0; 
        
        // organiser_price excluding admin_tax
        $booking_organiser_price    = [];
        $admin_tax                  = [];
        foreach($selected_tickets as $key => $value)
        {
            $booking[$key]['customer_id']       = $this->customer_id;
            $booking[$key]['customer_name']     = $customer['name'];
            $booking[$key]['customer_email']    = $customer['email'];
            $booking[$key]['organiser_id']      = $this->organiser_id;
            $booking[$key]['event_id']          = $request->event_id;
            $booking[$key]['ticket_id']         = $value['ticket_id'];
            $booking[$key]['quantity']          = $value['quantity'];
            $booking[$key]['status']            = 1; 
            $booking[$key]['created_at']        = Carbon::now();
            $booking[$key]['updated_at']        = Carbon::now();
            $booking[$key]['event_title']       = $data['event']['title'];
            $booking[$key]['event_category']    = $data['event']['category_name'];
            $booking[$key]['ticket_title']      = $value['ticket_title'];
            $booking[$key]['item_sku']          = $data['event']['item_sku'];
            $booking[$key]['currency']          = setting('regional.currency_default');

            $booking[$key]['event_repetitive']  = $data['event']->repetitive > 0 ? 1 : 0;

            // non-repetitive
            $booking[$key]['event_start_date']  = $data['event']->start_date;
            $booking[$key]['event_end_date']    = $data['event']->end_date;
            $booking[$key]['event_start_time']  = $data['event']->start_time;
            $booking[$key]['event_end_time']    = $data['event']->end_time;
            
            // repetitive event
            if($data['event']->repetitive)
            {
                $booking[$key]['event_start_date']  = $booking_date;
                $booking[$key]['event_end_date']    = $request->merge_schedule ? $request->booking_end_date : $booking_date;
                $booking[$key]['event_start_time']  = $request->start_time;
                $booking[$key]['event_end_time']    = $request->end_time;
            }
            
            foreach($tickets as $k => $v)
            {
                if($v['id'] == $value['ticket_id'])
                {
                    $price       = $v['price'];
                    break;
                }
            }
            $booking[$key]['price']         = $price * $value['quantity'];
            $booking[$key]['ticket_price']  = $price;

            // call calculate price
            $params   = [
                'ticket_id'         => $value['ticket_id'],
                'quantity'          => $value['quantity'],
            ];
    
            // calculating net price
            $net_price    = $this->calculate_price($params);

    
            $booking[$key]['tax']        = number_format((float)($net_price['tax']), 2, '.', '');
            $booking[$key]['net_price']  = number_format((float)($net_price['net_price']), 2, '.', '');
            
            // organiser price excluding admin_tax
            $booking_organiser_price[$key]['organiser_price']  = number_format((float)($net_price['organiser_price']), 2, '.', '');

            //  admin_tax
            $admin_tax[$key]['admin_tax']  = number_format((float)($net_price['admin_tax']), 2, '.', '');


            // if payment method is offline then is_paid will be 0
            if($request->payment_method == 'offline')
            {
                // except free ticket
                if(((int) $booking[$key]['net_price']))
                    $booking[$key]['is_paid'] = 0;
            }
            else
            {
                $booking[$key]['is_paid'] = 1;  
            }
            
        }
        
        // calculate commission 
        $this->calculate_commission($booking, $booking_organiser_price, $admin_tax);

        // if net price total == 0 then no paypal process only insert data into booking 
        foreach($booking as $k => $v)
        {
            $total_price  += (float)$v['net_price'];
            $total_price = number_format((float)($total_price), 2, '.', '');
        }

        // check if eligible for direct checkout
        $is_direct_checkout = $this->checkDirectCheckout($request, $total_price);
    
        // IF FREE EVENT THEN ONLY INSERT DATA INTO BOOKING TABLE 
        // AND DON'T INSERT DATA INTO TRANSACTION TABLE 
        // AND DON'T CALLING PAYPAL API
        if($is_direct_checkout)
        {
            $data = [
                'order_number' => time().rand(1,988),
                'transaction_id' => 0
            ];
            $flag =  $this->finish_booking($booking, $data);

            // in case of database failure
            if(empty($flag))
            {
                return error('Database failure!', Response::HTTP_REQUEST_TIMEOUT);
            }

            // redirect no matter what so that it never turns backreturn response
            $msg = __('eventmie-pro::em.booking_success');
            session()->flash('status', $msg);

            // if customer then redirect to mybookings
            $url = route('eventmie.mybookings_index');
            
            if(Auth::user()->hasRole('organiser'))
                $url = route('eventmie.obookings_index');
            
            if(Auth::user()->hasRole('admin'))
                $url = route('voyager.bookings.index');

            return response([
                'status'    => true,
                'url'       => $url,
                'message'   => $msg,
            ], Response::HTTP_OK);
        }    
        
        // return to paypal
        session(['booking'=>$booking]);
        
        /* CUSTOM */
        $this->set_payment_method($request, $booking);
        /* CUSTOM */

        return $this->init_checkout($booking);
    }

     /** 
     * Initialize checkout process
     * 1. Validate data and start checkout process
    */
    protected function init_checkout($booking)
    {   
        // add all info into session
        $order = [
            'item_sku'          => $booking[key($booking)]['item_sku'],
            'order_number'      => time().rand(1,988),
            'product_title'     => $booking[key($booking)]['event_title'],
            
            'price_title'       => '',
            'price_tagline'     => '',
        ];

        $total_price   = 0;

        foreach($booking as $key => $val)
        {
            $order['price_title']   .= ' | '.$val['ticket_title'].' | ';
            $order['price_tagline'] .= ' | '.$val['quantity'].' | ';
            $total_price            += $val['net_price'];
        }
        
        // calculate total price
        $order['price']             = $total_price;

        // set session data
        session(['pre_payment' => $order]);

        //CUSTOM
        return $this->multiple_payment_method($order);  
        //CUSTOM
        
        // return $this->paypal($order, setting('regional.currency_default'));
    }

    /** 
     * 4 Finish checkout process
     * Last: Add data to purchases table and finish checkout
    */
    protected function finish_checkout($flag = [])
    {
        // prepare data to insert into table
        $data                   = session('pre_payment');
        // unset extra columns
        unset($data['product_title']);
        unset($data['price_title']);
        unset($data['price_tagline']);
        

        $booking                = session('booking');
        
        // IMPORTANT!!! clear session data setted during checkout process
        // session()->forget(['pre_payment', 'booking']);
        
        
        /* CUSTOM */
        $payment_method         = (int)session('payment_method')['payment_method'];
        
        $authentication_3d      = (int)session()->get('authentication_3d', 0);

        // IMPORTANT!!! clear session data setted during checkout process
        session()->forget(['pre_payment', 'booking', 'payment_method', 'authentication_3d']);
        

        /* CUSTOM */  
        
        // if customer then redirect to mybookings
        $url = route('eventmie.mybookings_index');
        if(Auth::user()->hasRole('organiser'))
            $url = route('eventmie.obookings_index');
        
        if(Auth::user()->hasRole('admin'))
            $url = route('voyager.bookings.index');

        // if success 
        if($flag['status'])
        {
            $data['txn_id']             = $flag['transaction_id'];
            $data['amount_paid']        = $data['price'];
            unset($data['price']);
            $data['payment_status']     = $flag['message'];
            $data['payer_reference']    = $flag['payer_reference'];
            $data['status']             = 1;
            $data['created_at']         = Carbon::now();
            $data['updated_at']         = Carbon::now();
            $data['currency_code']      = setting('regional.currency_default');
            $data['payment_gateway']    = 'paypal';
            /* CUSTOM */
            $data['payment_gateway']    =  $payment_method == 2 ? 'Stripe' : 'PayPal';
            /* CUSTOM */            // insert data of paypal transaction_id into transaction table
        
            
            // insert data of paypal transaction_id into transaction table
            $flag                       = $this->transaction->add_transaction($data);

            $data['transaction_id']     = $flag; // transaction Id
            
            $flag = $this->finish_booking($booking, $data);

            // in case of database failure
            if(empty($flag))
            {
                
                $msg = __('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed');
                session()->flash('status', $msg);

                 /* CUSTOM */
                // if Stripe
                if($payment_method != 1 && $authentication_3d != 1)
                    return response(['status' => false, 'url'=>$url, 'message'=>$msg], Response::HTTP_OK);
                /* CUSTOM */

                return error_redirect($msg);
            }

            // redirect no matter what so that it never turns back
            $msg = __('eventmie-pro::em.booking_success');
            session()->flash('status', $msg);
            /* CUSTOM */
            // if Stripe
            if($payment_method != 1 && $authentication_3d != 1)
                return response(['status' => true, 'url'=>$url, 'message'=>$msg]);  
            /* CUSTOM */
            return success_redirect($msg, $url);
        }
        
        // if fail
        // redirect no matter what so that it never turns back
        $msg = __('eventmie-pro::em.payment').' '.__('eventmie-pro::em.failed');
        session()->flash('error', $msg);
        
         /* CUSTOM */
        // if Stripe
        if($payment_method != 1 && $authentication_3d != 1)
            return response(['status' => false, 'url'=>$url, 'message'=>$msg], Response::HTTP_OK);
        
        // redirection from 3d authentication card page when click on Go Back button then booking failed
        if($authentication_3d == 1)
            return redirect($url)->withErrors([__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed')]);
        /* CUSTOM */
        return error_redirect($msg);
    }

    // validate user post data
    protected function general_validation(Request $request)
    {
        $this->stripe_validation($request);
        
        return parent::general_validation($request);
    }
    
    /*====================== Payment Method Store In Session =======================*/

    protected function set_payment_method(Request $request, $booking = [])
    {
        $payment_method = [ 
            'payment_method' => $request->payment_method,
            'setupIntent'   => json_decode($request->setupIntent, true),
            'customer_email' => $booking[key($booking)]['customer_email'],
            'customer_name'  => $booking[key($booking)]['customer_name'],
            'event_title'    => $booking[key($booking)]['event_title'],
        ];

        session(['payment_method' => $payment_method]);
    }

     /*===========================multiple payment method ===============*/

    protected function multiple_payment_method($order = [])
    {   
        $url = route('eventmie.events_index');
        $msg = __('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed');
        
        $payment_method = (int)session('payment_method')['payment_method'];
        
        if($payment_method == 2)
        {
            if(empty(env('STRIPE_SECRET')))
                return response()->json(['status' => false, 'url'=>$url, 'message'=>$msg]); 

            return $this->stripe($order, setting('regional.currency_default'));
            
        }
        else
        {
            if(empty(setting('apps.paypal_secret')) || empty(setting('apps.paypal_client_id')))
                return response()->json(['status' => false, 'url'=>$url, 'message'=>$msg]); 
        
            return $this->paypal($order, setting('regional.currency_default'));
        }    
    }

    /*==========================Stripe Validation ====================*/

     
     /* ================ Stripe Integration ================ */
    
    protected function stripe($order = [], $currency = 'USD')
    {

        $customer_email = session('payment_method')['customer_email'];
        $event_title    = session('payment_method')['event_title'];
        $flag           = [];
    
        try
        {
            //current user
            $user = \Auth::user();

            // create customer
            if( empty($user->stripe_id) ){
                $user->createAsStripeCustomer();
            }

            // extra params and it is optional
            $extra_params = [
                "currency"    => $currency,
                "description" => $event_title,
            ];

            // payment method
            $paymentMethod  = session('payment_method')['setupIntent']['payment_method'];
            
            // add payment method
            $user->addPaymentMethod($paymentMethod);

            // payment
            // amount 
            $amount     = $order['price'] * 100;
            $amount     = (int) $amount;
            $stripe     = $user->charge($amount, $paymentMethod, $extra_params);
            
            if($stripe->status == 'succeeded')
            {   
                // set data
                if($stripe->charges['data'][0]->paid)
                {
                    $flag['status']             = true;
                    $flag['transaction_id']     = $stripe->charges['data'][0]->balance_transaction; // transation_id
                    $flag['payer_reference']    = $stripe->charges['data'][0]->id;                  // charge_id
                    $flag['message']            = $stripe->charges['data'][0]->outcome['seller_message']; // outcome message
                }
                else
                {   
                    $flag['status']             = false;
                    $flag['error']              = $stripe->charges['data'][0]->failure_message;
                }
            }
            else
            {
                $flag = [
                    'status'    => false,
                    'error'     => $stripe->status,
                ];
            }    

        } 

        // Laravel Cashier Incomplete Exception Handling for 3D Secure / SCA -> 4000000000003220 error card number
        catch (IncompletePayment $ex) {
            
            $redirect_url = route(
                'cashier.payment',
                [$ex->payment->id, 'redirect' => route('after3DAuthentication',['id' => $ex->payment->id ])]
            ); 

            return response()->json(['url' => $redirect_url, 'status' => true]);
        }

        // All Exception Handling like error card number
        catch (\Exception $ex)
        {
            // fail case
            $flag = [
                'status'    => false,
                'error'     => $ex->getMessage(),
            ];
        }
        
        return $this->finish_checkout($flag);
    } 

    protected function stripe_validation(Request $request)
    {
        // stripe
        if((int)$request->payment_method == 2)
        {
            $request->validate([
                'setupIntent'      => 'required',
            ]);
        }
    }

    // after redirect after3DAuthentication 

    public function after3DAuthentication($paymentIntent = null)
    {
        session(['authentication_3d' => 1]);
        
        $user   = \Auth::user();
        $flag   = [];
        
        try
        {
            
            $stripe = \Stripe\PaymentIntent::retrieve($paymentIntent, [
                'api_key' => env('STRIPE_SECRET'),
            ]);
            
            // successs 
            if($stripe->status == 'succeeded')
            {   
            
                // set data
                if($stripe->charges['data'][0]->paid)
                {
                    $flag['status']             = true;
                    $flag['transaction_id']     = $stripe->charges['data'][0]->balance_transaction; // transation_id
                    $flag['payer_reference']    = $stripe->charges['data'][0]->id;                  // charge_id
                    $flag['message']            = $stripe->charges['data'][0]->outcome['seller_message']; // outcome message
                }
                else
                {   
                    $flag['status']             = false;
                    $flag['error']              = $stripe->charges['data'][0]->failure_message;
                }
            }
            else
            {
                $flag = [
                    'status'    => false,
                    'error'     => $stripe->status,
                ];
            }
            
        }

        // All Exception Handling like error card number
        catch (\Exception $ex)
        {
            
            // fail case
            $flag = [
                'status'    => false,
                'error'     => $ex->getMessage(),
            ];
        }
        
        return $this->finish_checkout($flag);
    }
}
