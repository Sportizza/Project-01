<?php

namespace App\Models;

use Core\Model;
use PDO;
use PDOException;
use App\Auth;

class CustomerModel extends \Core\Model
{
    // Array of Error messages
    public $errors = [];

    //Start of Class constructor  
    public function __construct($data = [])
    {
        // Change the format of the key value pairs sent 
        // from the controller use in the model
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
    //End of Class constructor

    //Start of Displaying customer's bookings
    public static function customerBookings($id)
    {

        //Retrieving customers from the database
        $sql = 'SELECT feedback.feedback_id, booking.booking_id,booking.customer_user_id, booking.booking_date,
                booking.payment_status,booking.payment_method, booking.price_per_booking,booking.sports_arena_id,
                sports_arena_profile.sa_name,
                sports_arena_profile.category,sports_arena_profile.google_map_link,         
                TIME_FORMAT(time_slot.start_time, "%H" ":" "%i") AS startTime, 
                TIME_FORMAT(time_slot.end_time, "%H" ":" "%i") AS endTime
                FROM booking 
                LEFT JOIN feedback ON booking.booking_id = feedback.booking_id
                INNER JOIN booking_timeslot ON booking.booking_id = booking_timeslot.booking_id 
                INNER JOIN time_slot ON booking_timeslot.timeslot_id = time_slot.time_slot_id 
                INNER JOIN sports_arena_profile ON booking.sports_arena_id = sports_arena_profile.sports_arena_id 
                
                WHERE booking.customer_user_id=:id AND `booking`.`security_status`="active"
                ORDER BY booking.booking_date DESC';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }
    //End of Displaying customer's bookings

    //Start of Displaying customer's favourite list
    public static function customerFavouriteList($id)
    {

        //Retrieving favourite lists from the database
        $sql = 'SELECT favourite_list.fav_list_id, sports_arena_profile.sports_arena_id, sports_arena_profile.sa_name, sports_arena_profile.category, sports_arena_profile.location 
        FROM favourite_list
        INNER JOIN customer_profile ON  favourite_list.customer_profile_id=customer_profile.customer_profile_id
        INNER JOIN favourite_list_sports_arena ON favourite_list.fav_list_id = favourite_list_sports_arena.fav_list_id 
        INNER JOIN sports_arena_profile ON favourite_list_sports_arena.sports_arena_id = sports_arena_profile.sports_arena_id 
        WHERE customer_profile.customer_user_id=:id AND favourite_list_sports_arena.security_status="active"
        AND sports_arena_profile.security_status="active"';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }
    //End of Displaying customer's favourite list

    //Start of Displaying customer's notifications
    public static function customerNotification($id)
    {
        //Retrieving customer notifications from the database
        $sql = 'SELECT link, subject,description, DATE(date) as date , TIME_FORMAT( TIME(date) ,"%H" ":" "%i") as time, notification_id , notification_status
        FROM notification WHERE user_id=:id
        AND notification.security_status="active"
        ORDER BY date DESC,time DESC';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }
    //End of Displaying customer's notifications

    //Start of updating notification status 
    public static function updateNotification($id)
    {
        //Create a new database connection
        $db = static::getDB();

        //Retrieving notifications from the database
        $sql = 'UPDATE notification 
        SET notification_status="read"  
        WHERE notification_id=:id';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':notification_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }
    //End of updating notifications status 

    //Start of displaying sports arena timeslot
    public static function customerViewTimeSlots($arena_id)
    {
        //Retrieving sports arena timeslot from the database
        $sql = 'SELECT DISTINCT time_slot.time_slot_id,TIME_FORMAT(time_slot.start_time, "%H:%i") AS startTime,
        TIME_FORMAT(time_slot.end_time, "%H:%i") AS endTime,time_slot.price,facility.facility_name,
        sports_arena_profile.payment_method
        FROM time_slot
        INNER JOIN facility ON time_slot.facility_id= facility.facility_id
        INNER JOIN sports_arena_profile ON facility.sports_arena_id= sports_arena_profile.sports_arena_id
        Left JOIN booking_timeslot ON time_slot.time_slot_id =booking_timeslot.timeslot_id
        Left JOIN booking ON booking_timeslot.booking_id=booking.booking_id
        WHERE time_slot.time_slot_id NOT IN
                                            (SELECT booking_timeslot.timeslot_id 
                                            FROM booking 
                                            INNER JOIN booking_timeslot ON booking.booking_id=booking_timeslot.booking_id 
                                            WHERE ((booking.booking_date=CURRENT_DATE()) OR 
                                            (payment_status="pending" AND booked_date +INTERVAL 30 MINUTE > CURRENT_TIMESTAMP))
                                            AND booking_timeslot.security_status="active")
        AND time_slot.manager_sports_arena_id=:arena_id 
        AND time_slot.security_status="active" 
        AND time_slot.start_time > CURRENT_TIME() 
        GROUP BY time_slot.time_slot_id
        ORDER BY time_slot.start_time';

        // payment_status="pending" 
        // AND booked_date> CURRENT_TIMESTAMP

        // have to change this is wrong we use it for testing

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the sports arena id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }
    //End of Displaying sports arena timeslot

    public static function customerSearchTimeSlotsDate($arena_id, $date)
    {

        $db = static::getDB();
        //Retrieving sports arena timeslot from the database
        
        //Chaning the date format
        $current_date = date('Y-m-d');

        //If selected date is current date, execute the following
        if ($date != $current_date) {

            //Retrieving sports arena timeslots
                $sql = 'SELECT DISTINCT time_slot.time_slot_id,TIME_FORMAT(time_slot.start_time, "%H:%i") AS startTime,
                TIME_FORMAT(time_slot.end_time, "%H:%i") AS endTime,time_slot.price,facility.facility_name,
                sports_arena_profile.payment_method
                FROM time_slot
                INNER JOIN facility ON time_slot.facility_id= facility.facility_id
                INNER JOIN sports_arena_profile ON facility.sports_arena_id= sports_arena_profile.sports_arena_id
                Left JOIN booking_timeslot ON time_slot.time_slot_id =booking_timeslot.timeslot_id
                Left JOIN booking ON booking_timeslot.booking_id=booking.booking_id
                WHERE time_slot.time_slot_id NOT IN
                                                    (SELECT booking_timeslot.timeslot_id 
                                                    FROM booking 
                                                    INNER JOIN booking_timeslot ON booking.booking_id=booking_timeslot.booking_id 
                                                    WHERE ((booking.booking_date=:date) OR 
                                                    (payment_status="pending" AND booked_date +INTERVAL 30 MINUTE > CURRENT_TIME))
                                                    AND booking_timeslot.security_status="active")
                AND time_slot.manager_sports_arena_id=:arena_id 
                AND time_slot.security_status="active" 
                GROUP BY time_slot.time_slot_id
                ORDER BY time_slot.start_time;';

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':date', $date, PDO::PARAM_STR);
                $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
                $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
            $stmt->execute();

        }

        //If selected date is not the current date, execute the following
        else {

            //Retrieving sports arena timeslots
            $sql = 'SELECT DISTINCT time_slot.time_slot_id,TIME_FORMAT(time_slot.start_time, "%H:%i") AS startTime,
            TIME_FORMAT(time_slot.end_time, "%H:%i") AS endTime,time_slot.price,facility.facility_name,
            sports_arena_profile.payment_method
            FROM time_slot
            INNER JOIN facility ON time_slot.facility_id= facility.facility_id
            INNER JOIN sports_arena_profile ON facility.sports_arena_id= sports_arena_profile.sports_arena_id
            INNER JOIN booking_timeslot ON time_slot.time_slot_id =booking_timeslot.timeslot_id
            INNER JOIN booking ON booking_timeslot.booking_id=booking.booking_id
            WHERE time_slot.time_slot_id NOT IN
                                                (SELECT booking_timeslot.timeslot_id 
                                                FROM booking 
                                                INNER JOIN booking_timeslot ON booking.booking_id=booking_timeslot.booking_id 
                                                WHERE ((booking.booking_date=CURRENT_DATE()) AND 
                                                (payment_status="pending" AND booked_date +INTERVAL 30 MINUTE > CURRENT_TIMESTAMP))
                                                AND booking_timeslot.security_status="active")
            AND time_slot.manager_sports_arena_id=:arena_id 
            AND time_slot.security_status="active" 
            AND time_slot.start_time > CURRENT_TIME() 
            GROUP BY time_slot.time_slot_id
            ORDER BY time_slot.start_time';

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
             $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
                $stmt->execute();

        }
        
        $output = "";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "
            <li id={$row["time_slot_id"]} class='hideDetails'>
            <div class='row'>
                <span class='s-time'>{$row["startTime"]}</span>&nbsp;-
                <span class='e-time'>{$row["endTime"]}</span>
            </div>
            <div class='row'>
                <span class='facility'>{$row["facility_name"]}</span>
            </div>
            <div class='row'>
                <span class='price'>LKR {$row["price"]}</span>

            </div>

            <!-- toggle button -->
            <div class='payment_cart'>";
                
            if ($row["payment_method"]=='card'){
                $output .= "<div class='toggle-button-cover'>
                <div class='button-cover'>
                    <div class='button r' id='button-1'>
                        <input type='checkbox' class='checkbox' name='paymentMethod' value='card' checked>
                       
                        <div class='layer1'>card</div>
                        
                    </div>
                </div>
            </div>";
            } elseif ($row["payment_method"]=='cash'){    
                
                $output .= "<div class='toggle-button-cover'>
                <div class='button-cover'>
                    <div class='button r' id='button-1'>
                        <input type='checkbox' class='checkbox' name='paymentMethod' value='cash' checked>
                        
                        <div class='layer1'>cash</div>
                    </div>
                </div>
            </div>";
            } elseif ($row["payment_method"]=='both'){
                $output .= "<div class='toggle-button-cover'>
                <div class='button-cover'>
                <div class='button r' id='button-1'>
                        <input type='checkbox' class='checkbox' name='paymentMethod' onclick='paymentclick()' value='card' checked>
                        <div class='knobs'></div>
                        <div class='layer'></div>
                    </div>
                </div>
            </div>";
            }
                
            $output .= "<div>
                    <button class='removeItem' value={$row["time_slot_id"]} type='button'>
                        <i class='fas fa-cart-plus'></i></button>
                </div>
            </div>
            <input type='hidden' name='timeSlotId' value={$row["time_slot_id"]}>
            <input type='date' name='bookingDate' class='bookingDatehidden' value={$date} style='display: none;'>
        </li>";
        }

        return $output;
    }
    //End of Displaying sports arena timeslot


    //Start of displaying sports arena details
    public static function customerViewArenaDetails($arena_id)
    {
        //Retrieving sports arena profile from the database
        $sql = 'SELECT *
                 FROM  sports_arena_profile INNER JOIN sports_arena_profile_photo ON sports_arena_profile.s_a_profile_id = sports_arena_profile_photo.sa_profile_id 
                WHERE s_a_profile_id=:arena_id';
        // have to change this is wrong we use it for testing

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the arena id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }
    //End of displaying sports arena details


    //Start adding a sports arena to the favourite list 
    public static function customerAddFavoriteList($arena_id, $customer_id)
    {
        //Retrieving favoorute list id  from the database
        $sql = 'SELECT favourite_list.fav_list_id
                FROM  customer_profile
                INNER JOIN favourite_list ON customer_profile.customer_profile_id=favourite_list.customer_profile_id
                WHERE customer_profile.customer_user_id=:id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':id', $customer_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();

        //Assigning the favourite list it from the fetched PDO
        $favorite_list_id = $result[0]->fav_list_id;
        //Why var_dump?
        var_dump($favorite_list_id);

        //Retrieving all the sports arenas in favorite list
        $sql = 'SELECT sports_arena_id
        FROM  favourite_list_sports_arena
        WHERE fav_list_id=:favorite_list_id AND sports_arena_id=:arena_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the favourite list id, arena id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':favorite_list_id', $favorite_list_id, PDO::PARAM_STR);
        $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $spArenaExists = $stmt->fetchAll();

        //If there's no such sports arena in the favourite list
        if (!$spArenaExists) {

            //Inserting the sports arenas to favorite list
            $sql = 'INSERT INTO favourite_list_sports_arena (fav_list_id,sports_arena_id)
        VALUES (:favorite_list_id,:arena_id);';

            
            $stmt = $db->prepare($sql);

            //Binding the sports arena id and favourite list id Converting retrieved data from database into PDOs
            $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
            $stmt->bindValue(':favorite_list_id', $favorite_list_id, PDO::PARAM_STR);
            $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            return ($stmt->execute());
        }
        else{
            $sql = 'UPDATE favourite_list_sports_arena 
                SET security_status="active"
                WHERE fav_list_id=:favorite_list_id AND sports_arena_id=:arena_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
            $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
            $stmt->bindValue(':favorite_list_id', $favorite_list_id, PDO::PARAM_STR);
            $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            return ($stmt->execute());
        }
    }


    public static function customerCancelBooking($booking_id)
    {
        //update booking security status as a inactive
        $sql = 'UPDATE booking 
                SET security_status="inactive"
                WHERE booking_id=:booking_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();


        //update booking_timeslot security  status as a inactive
        $sql = 'UPDATE booking_timeslot 
                SET security_status="inactive"
                WHERE booking_id=:booking_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        return $stmt->execute();
    }



    public static function customerDeleteBooking($booking_id)
    {
        //update booking status as a inactive
        $sql = 'UPDATE booking 
                SET security_status="inactive"
                WHERE booking_id=:booking_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function customerDeleteFavoriteArena($fav_list_id, $arena_id)
    {
        //update booking status as a inactive
        $sql = 'UPDATE favourite_list_sports_arena 
                SET security_status="inactive"
                WHERE fav_list_id=:fav_list_id AND sports_arena_id=:arena_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':fav_list_id', $fav_list_id, PDO::PARAM_INT);
        $stmt->bindValue(':arena_id', $arena_id, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public static function customerAddFeedback($feedback)
    {
        //insert query for add feedbacks
        $sql = 'INSERT INTO feedback(booking_id,feedback_rating,sports_arena_id,description,customer_user_id)
        VALUES(:booking_id,:feedback_rating,:sports_arena_id,:description,:customer_user_id)';

        // get database connection
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':booking_id', $feedback["booking_id"], PDO::PARAM_INT);
        $stmt->bindValue(':feedback_rating', $feedback["rate"], PDO::PARAM_INT);
        $stmt->bindValue(':sports_arena_id', $feedback["arena_id"], PDO::PARAM_STR);
        $stmt->bindValue(':description', $feedback["rating_description"], PDO::PARAM_STR);
        $stmt->bindValue(':customer_user_id', $feedback["customer_id"], PDO::PARAM_INT);



        return $stmt->execute();
    }

    public static function customerAddToCart($customer_id,$timeslot_id, $booking_date, $payment_method)
    {
        $sql = 'SELECT time_slot.start_time, time_slot.end_time,
        time_slot.price,time_slot.facility_id,time_slot.manager_sports_arena_id
        FROM time_slot   
        WHERE time_slot.security_status="active"
        AND time_slot.time_slot_id=:timeslot_id';

        // get database connection
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':timeslot_id', $timeslot_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
        $price = $result['price'];
        $facility_id= $result['facility_id'];
        $arena_id = $result['manager_sports_arena_id'];
        //Assigning the fetched PDOs to result

        //insert query for add feedbacks
        $sql2 = 'INSERT INTO `booking`(`customer_user_id`, `booking_date`, 
        `payment_method`, `price_per_booking`, `facility_id`, 
        `sports_arena_id`) VALUES 
        (:customer_user_id,:booking_date,:payment_method,:price,:facility_id,
        :sports_arena_id)';

        // get database connection
        $stmt2 = $db->prepare($sql2);
        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt2->bindValue(':customer_user_id', $customer_id, PDO::PARAM_INT);
        $stmt2->bindValue(':booking_date', $booking_date, PDO::PARAM_STR);
        $stmt2->bindValue(':payment_method', $payment_method, PDO::PARAM_STR);
        $stmt2->bindValue(':price', $price, PDO::PARAM_INT);
        $stmt2->bindValue(':facility_id', $facility_id, PDO::PARAM_INT);
        $stmt2->bindValue(':sports_arena_id', $arena_id, PDO::PARAM_INT);
        $stmt2->execute();
        
        $sql3 = 'SELECT booking.booking_id from booking ORDER BY booking.booking_id DESC LIMIT 1';
        $stmt3 = $db->prepare($sql3);
        $stmt3->execute();
        $result3 = $stmt3->fetch(PDO::FETCH_ASSOC);
        $booking_id = $result3['booking_id'];
       
        $sql4 = 'INSERT INTO `booking_timeslot`(`timeslot_id`, `booking_id`) VALUES 
        (:timeslot_id,:booking_id)';

        // get database connection
        $stmt4 = $db->prepare($sql4);
        //Binding the timeslot id and booking id Converting retrieved data from database into PDOs
        
        $stmt4->bindValue(':timeslot_id', $timeslot_id, PDO::PARAM_INT);
        $stmt4->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt4->execute();
        return $arena_id;
    }

    public static function customerCartView($id)
    {
        // get database connection
        $db = static::getDB();

        $sql2='SELECT booking.price_per_booking, booking.booking_id, time_slot.start_time,time_slot.end_time, 
        sports_arena_profile.sa_name, sports_arena_profile.category, sports_arena_profile.location,
        booking.booked_date,booking.payment_method, facility.facility_name, DATE(booking.booking_date) AS booking_date
        FROM booking
        INNER JOIN booking_timeslot ON booking.booking_id=booking_timeslot.booking_id
        INNER JOIN time_slot ON booking_timeslot.timeslot_id=time_slot.time_slot_id
        INNER JOIN sports_arena_profile ON booking.sports_arena_id=sports_arena_profile.sports_arena_id
        INNER JOIN facility ON booking.facility_id= facility.facility_id
        WHERE booking_timeslot.security_status="active" AND booking.payment_status="pending"
        AND booking.customer_user_id=:user_id AND DATE(booking.booked_date)=DATE(CURRENT_TIMESTAMP)
        AND TIME(booking.booked_date) + INTERVAL 30 MINUTE > TIME(CURRENT_TIMESTAMP) ';

        $stmt = $db->prepare($sql2);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':user_id', $id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }

    public static function customerRefundDeltails($booking_id)
    {
        // get database connection
        $db = static::getDB();


        $sql2='SELECT * FROM booking WHERE booking_id=:booking_id';

        // AND booking.booked_date >= :prev_time AND booking.booked_date <=:next_time
        $stmt = $db->prepare($sql2);

        //Binding the customer id and Converting retrieved data from database into PDOs

        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }



    public static function customerRequestRefund($post)
    {
        // get database connection
        $db = static::getDB();


        $sql2='INSERT INTO refund(payment_id,invoice_id,booking_id,
        customer_user_id,account_no,benficiary_name,branch_name,bank_name,
        refund_status,refund_amount) VALUES (:payment_id, :invoice_id, :booking_id,:customer_user_id,
        :account_no,:benficiary_name,:branch_name,:bank_name,:refund_status,:refund_amount)';

        // AND booking.booked_date >= :prev_time AND booking.booked_date <=:next_time
        $stmt = $db->prepare($sql2);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':payment_id', $post['payment_id'], PDO::PARAM_INT);
        $stmt->bindValue(':invoice_id', $post['invoice_id'], PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', $post['booking_id'], PDO::PARAM_INT);
        $stmt->bindValue(':customer_user_id', $post['customer_user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':account_no', $post['accountNumber'], PDO::PARAM_INT);
        $stmt->bindValue(':benficiary_name', $post['benificiaryName'], PDO::PARAM_STR);
        $stmt->bindValue(':branch_name', $post['branchName'], PDO::PARAM_STR);
        $stmt->bindValue(':bank_name', $post['bankName'], PDO::PARAM_STR);
        $stmt->bindValue(':refund_status', "unpaid", PDO::PARAM_STR);
        $stmt->bindValue(':refund_amount', $post['amount'], PDO::PARAM_INT);
        // $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        return $result;
    }


    public static function customerPaymentSuccess($user_id,$user)
    {

        $db = static::getDB();

        $sql7 = 'INSERT INTO `payment` (`net_amount`,`customer_user_id`) VALUES (0,:user_id)';
        $stmt = $db->prepare($sql7);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $sql2 = 'SELECT `payment_id` FROM `payment` ORDER BY `payment_id` DESC LIMIT 1;';
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute();
        $result1 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $payment_id = $result1["payment_id"];

        $sql2 = 'SELECT booking.booking_id
        FROM booking
        INNER JOIN booking_timeslot ON booking.booking_id=booking_timeslot.booking_id
        INNER JOIN time_slot ON booking_timeslot.timeslot_id=time_slot.time_slot_id
        INNER JOIN sports_arena_profile ON booking.sports_arena_id=sports_arena_profile.sports_arena_id
        INNER JOIN facility ON booking.facility_id= facility.facility_id
        WHERE booking_timeslot.security_status="active" AND booking.payment_status="pending"
        AND booking.customer_user_id=:user_id AND DATE(booking.booked_date)=DATE(CURRENT_TIMESTAMP)
        AND TIME(booking.booked_date) + INTERVAL 30 MINUTE > TIME(CURRENT_TIMESTAMP) ';

        $stmt = $db->prepare($sql2);

        //Binding the customer id and Converting retrieved data from database into PDOs
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        //Assigning the fetched PDOs to result
        $result = $stmt->fetchAll();
        $len = count($result);

        $total_amount = 0;
        $total_card=0;
        $total_cash=0;

        for ($x = 0; $x < $len; $x++) {
           $booking_id = $result[$x][0];

        NotificationModel::addNotificationBookingSuccess($user, $booking_id);

        $sql4 = 'SELECT booking.price_per_booking, booking.booking_date ,booking.payment_method, facility.facility_name, 
        `time_slot`.`start_time`,
        `time_slot`.`end_time`, sports_arena_profile.sa_name
        FROM booking 
        INNER JOIN `facility` ON `facility`.facility_id = booking.facility_id
        INNER JOIN `sports_arena_profile` ON `sports_arena_profile`.sports_arena_id = `booking`.sports_arena_id
        INNER JOIN `booking_timeslot` ON `booking_timeslot`.`booking_id`= `booking`.`booking_id`
        INNER JOIN `time_slot` ON `time_slot`.`time_slot_id`=`booking_timeslot`.`timeslot_id`
        WHERE booking.booking_id =:booking_id';

        $stmt4 = $db->prepare($sql4);
        $stmt4->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);

        $stmt4->execute();

        $result1 = $stmt4->fetch(PDO::FETCH_ASSOC);

            //Obtaining the administratoin staff user details retrieved from result1
            $amount = $result1["price_per_booking"];
            $arena_name = $result1["sa_name"];
            $facility_name = $result1["facility_name"];
            $booking_date = $result1["booking_date"];
            $start_time = $result1["start_time"];
            $end_time = $result1["end_time"];
            $payment_method= $result1["payment_method"];

            $total_amount = $total_amount + $amount;

            if($payment_method=="cash"){
                $total_cash=$total_cash+$amount;
                $sql5 = 'INSERT INTO `invoice` (`payment_method`, `net_amount`) VALUES ("cash", :amount)';
            $stmt = $db->prepare($sql5);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
            
            $stmt->execute();
            }

            else{
                $total_card=$total_card+$amount;
                $sql5 = 'INSERT INTO `invoice` (`payment_method`, `net_amount`,`payment_id`) 
                VALUES ("card", :amount, :payment_id)';
            $stmt = $db->prepare($sql5);
             
            $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
    
            $stmt->execute();
            }
           
            $sql6 = 'SELECT `invoice_id` FROM `invoice` ORDER BY `invoice_id` DESC LIMIT 1;';

            $stmt6 = $db->prepare($sql6);
            $stmt6->execute();

            //Converting retrieved data from database into PDOs
            $result1 = $stmt6->fetch(PDO::FETCH_ASSOC);
            //Obtaining the user id retrieved from result1
            $invoice_id = $result1["invoice_id"];
            //Updating status of the bookings in the database

            if( $payment_method=="card"){
                $sql = 'UPDATE `booking` SET `payment_status`="paid", `invoice_id`=:invoice_id, `customer_user_id`=:user_id
                WHERE `booking_id`=:booking_id';
            }
            else{
                $sql = 'UPDATE `booking` SET `payment_status`="unpaid", `invoice_id`=:invoice_id, `customer_user_id`=:user_id
                WHERE `booking_id`=:booking_id';
            }
            

            $db = static::getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
            $stmt->bindValue(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

        }

        if($total_card==0){
            $sql = 'DELETE FROM `payment`
            WHERE `payment_id`=:payment_id';
       
            $stmt = $db->prepare($sql);
            
            $stmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
            $stmt->execute();
        }
        else{
        $sql = 'UPDATE `payment` SET `net_amount`=:total_amount
        WHERE `payment_id`=:payment_id';
   
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':total_amount', $total_card, PDO::PARAM_INT);
        $stmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
        $stmt->execute();
        }
        
        
        $sql10= 'SELECT `invoice_id` FROM `invoice` ORDER BY `invoice_id` DESC LIMIT 1;';

            $stmt6 = $db->prepare($sql10);
            $stmt6->execute();

            //Converting retrieved data from database into PDOs
            $result1 = $stmt6->fetch(PDO::FETCH_ASSOC);
            //Obtaining the user id retrieved from result1
            $invoice_id = $result1["invoice_id"];

            return ($invoice_id);
    }




    public static function customerRemoveTimeSlotFromCart($booking_id)
    {
        $sql = 'DELETE booking 
                WHERE booking_id=:booking_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function customerRefundAvailability($booking_id)
    {

       
        $sql = 'SELECT  * FROM refund 
                WHERE booking_id=:booking_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //Binding the customer id and Converting retrieved data from database into PDOs
        
        $stmt->bindValue(':booking_id', $booking_id, PDO::PARAM_INT);

        $stmt->execute();
        $result= $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        if(empty($result)){
            return false;
        }
        else{
            return true;
        }
        
    }



      // Start of removing a booking from cart
      public static function customerClearBooking($booking_id)
      {
          try {
              //Create a new database connection
              $db = static::getDB();
  
              //Start transaction
              $db->beginTransaction();
  
              //Removing the booking from booking table
              $sql = 'UPDATE booking 
              SET security_status="inactive" 
              WHERE booking_id=:id';
  
              $stmt = $db->prepare($sql);
              $stmt->bindValue(':id', $booking_id, PDO::PARAM_INT);
              $stmt->execute();
  
              //Removing the booking from booking_timeslot table
              $sql = 'UPDATE booking_timeslot 
              SET security_status="inactive" 
              WHERE booking_id=:id';
  
              $stmt = $db->prepare($sql);
              $stmt->bindValue(':id', $booking_id, PDO::PARAM_INT);
              $stmt->execute();
  
              //End transaction
              $db->commit();
              return true;
          } catch (PDOException $e) {
              $db->rollback();
              throw $e;
          }
      }
      // End of clearing a booking from cart



      public static function customerBookingCalenderView($arena_id)
    {

        $sql = 'SELECT  COUNT(booking_id) AS bookingCount,(CURRENT_DATE-(booking_date)) as remainingDates FROM booking 
                WHERE security_status="active" AND booking_date> CURRENT_DATE AND sports_arena_id=:id
                GROUP BY booking_date';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $arena_id, PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
        
       
    }
    
}
