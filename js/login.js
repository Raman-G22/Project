$(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const loginData = {
            email: $('#email').val(),
            password: $('#password').val()
        };
        
        $.ajax({
            type: 'POST',
            url: 'php/login.php',
            data: JSON.stringify(loginData),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success' && response.token) {
                    localStorage.setItem('sessionToken', response.token);
                    window.location.href = 'profile.html';
                } else {
                    displayMessage('error', response.message);
                }
            },
            error: function() {
                displayMessage('error', 'An error occurred during login.');
            }
        });
    });

    function displayMessage(type, message) {
        const messageArea = $('#message-area');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        messageArea.html(<div class="alert ${alertClass}">${message}</div>);
    }
});