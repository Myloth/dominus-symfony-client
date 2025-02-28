import Routing from './app';

$(document).ready(function() {
    $('#gen_key').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: Routing.generate('admin_user_gen_secret_key'),
            success: function(response) {
                $("#edit_api_user_salt").val(response.key);
            }
        })
    })
})