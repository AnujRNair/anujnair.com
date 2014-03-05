$(document).ready(function() {

    $('.deleteUser').click(function() {
        if (!confirm('Please confirm you would like to delete this user')) {
            return false;
        }
    });

});