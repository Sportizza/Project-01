const username = document.getElementById('username');
const password = document.getElementById('password');
 
    
        // popup form section
    
        function openpopupform(){
            var form=document.getElementById("myForm");
            
            form.style.display = "block";
    
        }
    
        function closepopupform(){
            var form=document.getElementById("myForm");
            
            form.style.display = "none";
    
        }
    
    
        //right
        //popup editPrimaryPhoneNumber section
        function open_editPrimaryPhoneNumber(){
          var form=document.getElementById("popup_editPrimaryNumber");
          var profile=document.getElementById("myForm");
          profile.style.display="none"
          form.style.display = "block";
      }
      function close_editPrimaryPhoneNumber(){
        var form=document.getElementById("popup_editPrimaryNumber");
        var profile=document.getElementById("myForm");
        profile.style.display="block"
        form.style.display = "none";
    }
    
       //popup editSecondaryPhoneNumber section
    //    function open_editSecondaryPhoneNumber(){
    //     var form=document.getElementById("popup_editSecondaryNumber");
    //     var profile=document.getElementById("myForm");
    //     profile.style.display="none"
    //     form.style.display = "block";
    // }
    // function close_editSecondaryPhoneNumber(){
    //   var form=document.getElementById("popup_editSecondaryNumber");
    //   var profile=document.getElementById("myForm");
    //   profile.style.display="block"
    //   form.style.display = "none";
    // }
    
        //wrong
       //popup edit password section
    function open_editPassword(){
        var form=document.getElementById("popup_editPassword");
        var profile=document.getElementById("myForm");
        profile.style.display="none"
        form.style.display = "block";
    }
    function close_editPassword(){
      var form=document.getElementById("popup_editPassword");
      var profile=document.getElementById("myForm");
      profile.style.display="block"
      form.style.display = "none";
    }
    
    document.getElementById("ChangePassword").addEventListener("click", function(event){
      event.preventDefault();
      open_editPassword();
    });
    
    document.getElementById("No_EditPassword").addEventListener("click", function(event){
      event.preventDefault();
      close_editPassword();
    });
    
    // const oldPassword = document.getElementById('oldPassword');
    // const togglePassword1 = document.querySelector('#togglePassword1');
    // const showPassword1 = document.querySelector('#oldPassword');
   //Show password button
// togglePassword.addEventListener('click', function (e) {
//   alert("Hello")
//   // toggle the type attribute
//   const type = showPassword.getAttribute('type') === 'password' ? 'text' : 'password';
//   showPassword.setAttribute('type', type);
//   // toggle the eye / eye slash icon
//   this.classList.toggle('bi-eye');
// });

// const togglePassword = document.querySelector('#togglePassword');
const togglePassword1 = document.querySelector('#togglePassword1');
const togglePassword2 = document.querySelector('#togglePassword2');
const showOldPassword = document.querySelector('#oldPassword');
const showNewPassword = document.querySelector('#newPassword');

togglePassword1.addEventListener('click', function (e) {
  // toggle the type attribute
  // alert("Hello");
  const type = showOldPassword.getAttribute('type') === 'password' ? 'text' : 'password';
  showOldPassword.setAttribute('type', type);
  // toggle the eye / eye slash icon
  this.classList.toggle('bi-eye');
});


togglePassword2.addEventListener('click', function (e) {
  // toggle the type attribute
  const type = showNewPassword.getAttribute('type') === 'password' ? 'text' : 'password';
  showNewPassword.setAttribute('type', type);
  // toggle the eye / eye slash icon
  this.classList.toggle('bi-eye');
});


function validateOldPassword() {
  if (checkIfEmptyNext(showOldPassword)) return;
  if (!meetLengthNext(showOldPassword, 8, 255)) return;
  regEx = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;
  if (!matchWithRegExPassword(regEx, showOldPassword)) return;
  return true;
}

function validateNewPassword() {
if (checkIfEmptyNext(showNewPassword)) return;
if (!meetLengthNext(showNewPassword, 8, 255)) return;
regEx = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;
if (!matchWithRegExPassword(regEx, showNewPassword)) return;
return true;
}

    
    
    
    
    
    
        //popup sign out message
        function open_popup_signout_message(){
          var form=document.getElementById("popup_signout");
          
          form.style.display = "block";
      }
      function close_popup_signout_message(){
        var form=document.getElementById("popup_signout");
        
        form.style.display = "none";
    }
    
    
    
    
        //popup share section
        function open_popup_share(){
          var form=document.getElementById("popup_share");
            
          form.style.display = "block";
        }
        function close_popup_share(){
          var form=document.getElementById("popup_share");
            
          form.style.display = "none";
        }
    
        
        //popup cancel message
        function open_popup_cancel_message(){
          var form=document.getElementById("popup_cancel");
          
          form.style.display = "block";
      }
      function close_popup_cancel_message(){
        var form=document.getElementById("popup_cancel");
        
        form.style.display = "none";
    }
    
    
        //popup delete message
        function open_popup_delete_message(){
          var form=document.getElementById("popup_delete");
          
          form.style.display = "block";
      }
      function close_popup_delete_message(){
        var form=document.getElementById("popup_delete");
        
        form.style.display = "none";
    }
    
    
        //popup rate message
        function open_popup_rate_message(){
          var form=document.getElementById("popup_rate");
          
          form.style.display = "block";
      }
      function close_popup_rate_message(){
        var form=document.getElementById("popup_rate");
        
        form.style.display = "none";
    }
    
        //popup delete message for favorite list
        function open_popup_delete_message_favorite_list(){
          var form=document.getElementById("popup_delete_favorite_list");
          
          form.style.display = "block";
      }
      function close_popup_delete_message_favorite_list(){
        var form=document.getElementById("popup_delete_favorite_list");
        
        form.style.display = "none";
    }
    
    
    
          //popup notification section
          function open_popup_notification(){
            var form=document.getElementById("popup_notification");
              
            form.style.display = "block";
          }
          function close_popup_notification(){
            var form=document.getElementById("popup_notification");
              
            form.style.display = "none";
          } 
        
    
    // // set onclick button as a view booking button in the page loading process
    //       window.onload=function(){
    //         document.getElementById("view_booking_button").click();
    //     };
    
    
      // js for edit profile picture
      const imgDiv = document.querySelector(".editProPic");
      const img =document.querySelector("#form-profile-picture");
    const file=document.querySelector("#file");
    const uploadBtn=document.querySelector("#uploadBtn");
    
    //if user hover image div
    imgDiv.addEventListener("mouseenter",function(){
      uploadBtn.style.display="block"
    })
    
    //if user out from img div
    imgDiv.addEventListener("mouseleave",function(){
      uploadBtn.style.display="none"
    })
    
    //work form image showing function
    file.addEventListener("change",function(){
      //this refers to file upload
      const choosedFile = this.files[0];
      if(choosedFile){
        const reader= new FileReader();
         //file reader function
         reader.addEventListener("load",function(){
           img.setAttribute("src",reader.result);
         });
         reader.readAsDataURL(choosedFile);
    
      }
    })
