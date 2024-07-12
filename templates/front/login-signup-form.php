<?php
if (!defined('AUTHADILLO_ABSPATH')) 
{
    exit; // Exit if accessed directly.
}
?>
<span id="authadillo_login_form">
    <span class="error-message" id="login-error">{{error_message}}</span>
    <h2>Please Login</h2>
    <form method="post">
        <input type="hidden" name="authadillo_form_request_type" value="login">
        <br />
        <label>Enter Email</label>
        <br />
        <input type="username" name="username">
        <br />
        <label>Password</label>
        <br />
        <input type="password" name="password">
        <br /><br />
        <input type="submit"  value="login">
        <br />
    </form>
    <br /><a href="javascript:toggle_authadillo_signin_login_forms('signup');">Not a Member? Sign Up</a><br />
</span>
		
<span id="authadillo_signup_form">
    <span class="error-message" id="signup-error">{{error_message}}</span>
    <h2>Sign Up to Gain Access</h2>
    <form method="post">
        <input type="hidden" name="authadillo_form_request_type" value="signup">
        <br />
        <label>First Name</label>
        <br />
        <input type="firstname" name="firstname">
        <br />
        <label>Last Name</label>
        <br />
        <input type="lastname" name="lastname">
        <br />
        <label>Email</label>
        <br />
        <input type="email" name="username">
        <br />
        <label>Password</label>
        <br />
        <input type="password" name="password">
        <br /><br />
        <input type="submit" value="Sign Up">
        <br />
    </form>
    <br /><a href="javascript:toggle_authadillo_signin_login_forms('login');">Already a Member? Login</a><br />
</span>