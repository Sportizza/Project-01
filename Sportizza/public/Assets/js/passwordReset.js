
// const newPassword = document.getElementById('newPassword');
const togglePassword = document.querySelector('#togglePassword');
const showPassword = document.getElementById('newPassword');
//Add Form
const formUserPasswordReset = document.getElementById('formUserPasswordReset');

togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    // alert("Hello");
    const type = showOldPassword.getAttribute('type') === 'password' ? 'text' : 'password';
    showOldPassword.setAttribute('type', type);
    // toggle the eye / eye slash icon
    this.classList.toggle('bi-eye');
  });


formUserPasswordReset.addEventListener('submit', function (event) {
    // Prevent default behaviour
    event.preventDefault();
    if (
        validateNewPassword()
    ) {
        formUserPasswordReset.submit();
    }
});

function validatePasswordResetForm() {
    validateNewPassword();
}

function validateNewPassword() {
    let newPassword = document.getElementById("newPassword");
    // let newPassword = document.getElementById('newPassword');
    // alert(newPassword);
    if (checkIfEmptyNext(newPassword)) return;
    if (!meetLengthNext(newPassword, 8, 255)) return;
    regEx = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;
    if (!matchWithRegExPassword(regEx, newPassword)) return;
    return true;

}
