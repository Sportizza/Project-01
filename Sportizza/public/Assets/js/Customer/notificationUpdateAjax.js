$(document).ready(function () {
    $(".notification_row").click(function () {
        let notification_id = $(this).find(".notification-value").val();
        
        console.log(notification_id);
        $(this).css("font-weight","300");
        $.ajax({
            type: "POST",

            url: "http://localhost/Customer/updateNotification/" + notification_id
            
        })
    })
})