<?php

namespace App\Controllers;

use Core\View;
use \App\Auth;
use App\Models\CustomerModel;
use App\Models\EditProfileModel;
use App\Flash;
use App\Models\SignupModel;

use App\Models\NotificationModel;

class Customer extends Authenticated
{
    //Start of blocking a user after login
    //Blocking unauthorised access after login as a user
    protected function before()
    {
        if (Auth::getUser() == NULL) {
            $this->redirect('/login');
        }

        //Checking whether the user type is customer
        if (Auth::getUser()->type == 'Customer') {
            return true;
        }
        //Return to error page
        else {
            View::renderTemplate('500.html');
            return false;
        }
    }
    //End of blocking a user after login

    //Start of Landing page of customer
    public function indexAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $id = $current_user->user_id;
        //Assigning the customer's bookings 
        $bookings = CustomerModel::customerBookings($id);
        //Assigning the customer's favourite list 
        $favourie_list = CustomerModel::customerFavouriteList($id);
        //Assigning the customer's notifications 
        $notifications = CustomerModel::customerNotification($id);

        //Rendering the customers home view
        View::renderTemplate(
            'Customer/customerDashboardView.html',
            [
                'bookings' => $bookings, 'list' => $favourie_list,
                'notifications' => $notifications
            ]
        );
    }
    //End of Landing page of customer


    //Start of updating notification status
    public function updateNotificationAction()
    {
        $notification_id = $this->route_params['id'];
        $success = CustomerModel::updateNotification($notification_id);
        if ($success) {
            echo true;
        }
    }
    //End of updating notification status

    //Start of Cart page of customer
    public  function cartAction()
    {
        $current_user = Auth::getUser();
        $user_id = $current_user->user_id;
        $cart = CustomerModel::customerCartView($user_id);
     
        $cashSum = 0;
        $cardSum = 0;
        $allSum = 0;
        $i = 0;

        for ($i; $i < count($cart); $i++) {

            if ($cart[$i]->payment_method == "cash") {
                $cashSum += $cart[$i]->price_per_booking;
            } else {
                $cardSum += $cart[$i]->price_per_booking;
            }
        }
        $allSum = $cashSum + $cardSum;

        //Rendering the customers cart view
        View::renderTemplate('Customer/customerCartNewView.html', [
            'cart' => $cart,
            'allSum' => $allSum, 'cardSum' => $cardSum, 'cashSum' => $cashSum
        ]);
    }
    //End of Cart page of customer

    //Start of booking page of customer
    public function bookingAction()
    {
        //Assigning the sports arena's ID
        $id = $this->route_params['id'];

        //Assigning the sports arenas timeslots
        $timeSlots = CustomerModel::customerViewTimeSlots($id);
        //Assigning the sports arenas details
        $arenaDetails = CustomerModel::customerViewArenaDetails($id);
        $bookingsCount=CustomerModel::customerBookingCalenderView($id);
        $arenaDetails[0]->google_map_link = preg_replace('/\%\d\w/', ' , ', substr($arenaDetails[0]->google_map_link, 48));
        //Rendering the customers booking view
        
        View::renderTemplate(
            'Customer/customerBookingView.html',
            ['timeSlots' => $timeSlots, 'arenaDetails' => $arenaDetails, 'bookingsCount' => $bookingsCount]
        );

    }
    //End of booking page of customer

    //Start of booking page of customer
    public function searchtimeslotdateAction()
    {
        //Assigning the relevant variables
        $combined = $this->route_params['arg'];

        $combined = explode("__", $combined);
        $arena_id = $combined[0];
        $date = str_replace("_", "-", $combined[1]);

        //Assigning the sports arenas timeslots
        $timeSlots = CustomerModel::customerSearchTimeSlotsDate($arena_id, $date);

        echo $timeSlots;
    }
    //End of booking page of customer


    //Start of adding timeslots to customer by removing from the view
    public function hidebookingAction()
    {

        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $customer_id = $current_user->user_id;

        //Assigning the relevant variables
        $combined = $this->route_params['arg'];

        $combined = explode("__", $combined);
        $timeslot_id = $combined[0];
        $bookingDate = str_replace("_", "-", $combined[1]);
        $paymentMethod = $combined[2];

        //Adding timeslot to customer cart
        $addCart = CustomerModel::customerAddToCart($customer_id, $timeslot_id, $bookingDate, $paymentMethod);
        // $this->redirect("/customer/booking/$arena_id");
        if ($addCart) {
            echo true;
        }

    }
    //End of adding timeslots to customer by removing from the view



    //Start of cancel bookings from customer's my bookings view
    public function customercancelbookingAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();

        //Assigning booking id to variable
        $booking_id = $this->route_params['id'];
        $cancelbooking = CustomerModel::customerCancelBooking($booking_id);

        if ($cancelbooking) {
            NotificationModel::cancelNotificationBookingSuccess($current_user, $booking_id);
            $this->redirect("/customer");
        }
    }


    public function customerdeletebookingAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();

        //Assigning bookings related to customer
        $booking_id = $this->route_params['id'];
        $deletebooking = CustomerModel::customerDeleteBooking($booking_id);


        if ($deletebooking) {
            $this->redirect("/customer");
        }
    }


    public function customerdeletefavoritearenaAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();

        //Assigning bookings related to customer
        $arena_id = $this->route_params['id'];
        $favourie_list_id = $_POST['fav_list_id_input'];
        $deletebooking = CustomerModel::customerDeleteFavoriteArena($favourie_list_id, $arena_id);

        $this->redirect("/customer");
    }

    public function customeraddfeedbackAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();

        if(empty($_POST)){
            $this->redirect("/customer");
        }

        //Assigning bookings related to customer
        $addfeedback = CustomerModel::customerAddFeedback($_POST);
        $subject = "customer add feedback for arena";
        NotificationModel::addRatingNotificationForManager($_POST["arena_id"], $subject, $_POST["rating_description"]);
        NotificationModel::addRatingNotificationForAdministrationStaff($_POST["arena_id"], $subject, $_POST["rating_description"]);

        $this->redirect("/customer");
    }

    //End of cancel bookings from customer's my bookings view

    //Start of sending notification for all users after a card payment
    public function paymentsuccessAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
       
        $invoice_id=customerModel::customerPaymentSuccess($current_user->user_id,$current_user);
        // Calling the notification

        $this->redirect('/Customer');
    }

    //Start of adding sportsarena to Favourite list
    public function addtofavoritelistAction()
    {
        //Get the current user's details with session using Auth
        $customer_id = Auth::getUser()->user_id;
        //Adding sports arena to favorite list
        CustomerModel::customerAddFavoriteList($_POST['arena_id'], $customer_id);
        //redirect into customer boooking view
        $this->redirect('/Customer/booking/' . $_POST['arena_id']);
    }
    //End of adding sportsarena to Favourite list


    //Start of customer get refund
    public function refundAction()
    {
        if (CustomerModel::customerRefundAvailability($this->route_params['id'])) {

            View::renderTemplate(
                '500.html'
            );
        } else {
            $details = CustomerModel::customerRefundDeltails($this->route_params['id']);
            View::renderTemplate(
                'Customer/refund.html',
                ['details' => $details]
            );
        }
    }
    //End of customer get refund


    //Start of customer request refund
    public function customerrequestrefundAction()
    {
        $customer_id = Auth::getUser()->user_id;
        NotificationModel::refundRequestSuccessNotification($customer_id, $_POST["booking_id"]);
        CustomerModel::customerRequestRefund($_POST);
        $this->redirect('/Customer');
    }
    //End of customer request refund

    //Start of customer request refund
    public function customerremovetimeslotfromcart()
    {
        $booking_id = $this->route_params['id'];
        CustomerModel::customerRemoveTimeSlotFromCart($booking_id);
        $this->redirect('/Customer/cart');
    }
    //End of customer request refund


    // Start of removing a booking from visitor's cart 
    public function clearbookingAction()
    {
        //Assigning the relevant variables
        $booking_id = $this->route_params['id'];

        $clearedSlot = CustomerModel::customerClearBooking($booking_id);

        if ($clearedSlot) {
            echo true;
        }
    }
    // End of removing a booking from visitor's cart 

    //End of Administration Staff's cart view
    /*--------------------------------------------------------------------------------------------------*/
}
