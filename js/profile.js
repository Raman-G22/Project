$(function() {
    const token = localStorage.getItem('sessionToken');
    if (!token) {
        window.location.href = 'login.html';
        return;
    }
    function loadProfile() {
        $.ajax({
            type: 'GET',
            url: 'php/profile.php',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    $('#username').val(data.username);
                    $('#email').val(data.email);
                    $('#age').val(data.age);
                    $('#dob').val(data.dob);
                    $('#contact').val(data.contact);
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    logout();
                }
            }
        });
    }
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const profileData = {
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: $('#contact').val()
        };

        $.ajax({
            type: 'POST',
            url: 'php/profile.php',
            data: JSON.stringify(profileData),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            },
            success: function(response) {
                displayMessage('success', response.message);
            },
            error: function() {
                displayMessage('error', 'Failed to update profile.');
            }
        });
    });
    $('#logoutBtn').on('click', function() {
        logout();
    });

    function logout() {
        localStorage.removeItem('sessionToken');
        window.location.href = 'login.html';
    }
    
    function displayMessage(type, message) {
        const messageArea = $('#message-area');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        messageArea.html(<div class="alert ${alertClass}">${message}</div>);
        setTimeout(() => messageArea.html(''), 3000);
    }
    
    loadProfile();
});