jQuery(document).ready(function($) 
{
    var logoutButton = jQuery('#authadillo-logout-button');
    if (logoutButton.length) 
    {
        logoutButton.on('click', function() {
            authadillo_signout();
        });
    }
});


function toggle_authadillo_signin_login_forms(formval)
{
    if(formval == 'signup')
    {
        jQuery('#authadillo_login_form').hide();
        jQuery('#authadillo_signup_form').show();
    }
    else if(formval == 'login')
    {
        jQuery('#authadillo_signup_form').hide();
        jQuery('#authadillo_login_form').show();
    }
    else
    {
        //
    }
}

function authadillo_signout()
{
    jQuery.ajax({
        url: authadillo_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'authadillo_signout',
        },
        success: function(response) {
            if (response.success) {
                alert('Signed out successfully.');
                // Optionally redirect to home page or login page
                window.location.href = response.data.redirect_url;
            } else {
                alert('Sign out failed: ' + response.data.message);
            }
        },
        error: function() {
            alert('An error occurred while signing out.');
        }
    });
}