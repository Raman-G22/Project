$(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        const userData = {
            username: $('#username').val(),
            email: $('#email').val(),
            password: $('#password').val()
        };

        $.ajax({
            type: 'POST',
            url: 'php/register.php',
            data: JSON.stringify(userData),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success') {
                    displayMessage('success', response.message + ' Redirecting to login...');
                    $('#registerForm')[0].reset();
                    setTimeout(() => {
                         window.location.href = 'login.html';
                    }, 2000);
                } else {
                    displayMessage('error', response.message);
                }
            },
            error: function() {
                displayMessage('error', 'An error occurred during registration.');
            }
        });
    });

    function displayMessage(type, message) {
        const messageArea = $('#message-area');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        messageArea.html(<div class="alert ${alertClass}">${message}</div>);
    }
});