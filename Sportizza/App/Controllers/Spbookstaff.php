<?php

namespace App\Controllers;

use Core\View;
use App\Auth;
use App\Models\SpBookStaffModel;

class Spbookstaff extends Authenticated
{
    //Start of blocking a user after login
    //Blocking unauthorised access after login as a user
    protected function before()
    {
        //Checking whether the user type is booking handling staff
        if (Auth::getUser()->type == 'BookingHandlingStaff') {
            return true;
        }
        //Return to error page
        else {
            View::renderTemplate('500.html');
            return false;
        }
    }
    protected function after()
    {
    }
    //End of blocking a user after login

    //Start of Landing page of booking handling staff
    public function indexAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $id = $current_user->user_id;
        $arena_details = SpBookStaffModel::arenaProfileView($id);
        $arena_details['google_map_link'] = preg_replace('/\%\d\w/', ' , ', substr($arena_details['google_map_link'], 48));

        $notifications = SpBookStaffModel::saBookNotification($id);
        $count=0;
        foreach ($notifications as $notification){
            if($notification->notification_status == "unread")
            $count=$count+1;
            
        }

        //Rendering the booking handling staff's home view(sports arena profile)
        View::renderTemplate('BookHandlingStaff/bStaffProfileView.html',['arena_details' => $arena_details,
        'notificationsCount' => $count]);
    }
    //End of Landing page of booking handling staff

    //Start of manage View of booking handling staff
    public function managebookingsAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $id = $current_user->user_id;
        //Assigning the sports arena's bookings to view
        $bookings = SpBookStaffModel::saBookViewBookings($id);
        $notifications = SpBookStaffModel::saBookNotification($id);
        $count=0;
        foreach ($notifications as $notification){
            if($notification->notification_status == "unread")
            $count=$count+1;
            
        }

        //Rendering the booking handling's view booking view
        View::renderTemplate('BookHandlingStaff/bStaffViewBookingsView.html', ['bookings' => $bookings,'notificationsCount' => $count]);
    }
    //End of manage View of booking handling staff

    //Start of notifications of booking handling staff
    public  function sabookstaffnotificationAction()
    {
        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $id = $current_user->user_id;
        //Assigning the notifications related to user
        $notifications = SpBookStaffModel::saBookNotification($id);
        
        $count=0;
        foreach ($notifications as $notification){
            if($notification->notification_status == "unread")
            $count=$count+1;
            
        }

        //Rendering the booking handling staff's notification view
        View::renderTemplate('BookHandlingStaff/bStaffNotificationView.html', ['notifications' => $notifications,'notificationsCount' => $count]);
    }
    //End of View of booking handling staff   
    
    //Start of updating notification status
    public function updateNotificationAction()
    {
        $notification_id = $this->route_params['id'];
        
        $success = SpBookStaffModel::updateNotification($notification_id);

        //Get the current user's details with session using Auth
        $current_user = Auth::getUser();
        $id = $current_user->user_id;
        //Assigning the notifications related to user
        $notifications = SpBookStaffModel::saBookNotification($id);
        
        $count=0;
        foreach ($notifications as $notification){
            if($notification->notification_status == "unread")
            $count=$count+1;
        }
        
        if ($success) {
            echo $count;
        }
    }
    //End of updating notification status
    
}
