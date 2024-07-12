<?php 

if (!defined('AUTHADILLO_ABSPATH')) {
    exit; // Exit if accessed directly.
}

class AuthadilloAuthentication
{	
	public $error_message;
	
	function __construct()		
	{
		$this->error_message = false;
	}

	function GetAuthStatus()
	{
		if(isset($_SESSION['authadillo_user_auth']) && !empty($_SESSION['authadillo_user_auth']))
		{
			//Admin is authenticated
			return true;
		}
		else
		{
			//Admin is NOT authenticated
			return false;
		}
	}
	
	/* Check for Login or Signup */
	function CheckFormRequestType()
	{
		if(isset($_POST['authadillo_form_request_type']))
		{
			if($_POST['authadillo_form_request_type'] == 'login')
			{
				$this->SignIn();
			}
			else if($_POST['authadillo_form_request_type'] == 'signup')
			{
				$this->RegisterNewMember();
			}
		}
	}
	
	function SignIn()
	{
		global $wpdb;
		
		$is_valid = true;
				
		if(isset($_POST['username']))
		{
			if($_POST['username'] != '')
			{
				$username = $wpdb->_real_escape($_POST['username']);
			}
			else
			{
				$is_valid = false;				
			}			
		}
		
		if(isset($_POST['password']))
		{
			if($_POST['username'] != '')
			{
				$password = $wpdb->_real_escape($_POST['password']);
			}
			else
			{
				$is_valid = false;				
			}
		}
		
		if($is_valid)
		{
			$auth_sql = "SELECT * FROM {$wpdb->prefix}authadillo_members WHERE username = '$username'";
			$results = $wpdb->get_results( $auth_sql, 'ARRAY_A' );

			if(isset($results[0]['password']) && password_verify($password, $results[0]['password']))
			{				
				$_SESSION['authadillo_user_auth'] = $results[0]['id'].".".$results[0]['id_hash'];
			}
			else
			{
				$is_valid = false;				
			}			
		}
		
		$errors = "Your login is incorrect";
		
		//Send Error message on invalid sign in
		if(!$is_valid)
		{
			$this->error_message = $errors;	
		}		
	}
	
	function SignOut()
	{
		unset($_SESSION['authadillo_user_auth']);			
		session_destroy();
	}

	function ForceLoginForm($content) 
	{ 
		// Check if we're inside the main loop in a single post page.
		if ( in_the_loop() && is_main_query() ) 
		{
			$error_msg = ($this->error_message) ? $this->error_message : " ";

			$form_template = get_authadillo_template_part(array('type' => 'front', 'name' => 'login-signup-form'));
			echo authadillo_template_merges(array('error_message' => $error_msg), $form_template);

			return;
		} 

		return $content;	
	}

	function GenerateNavMenu($the_content)
	{
		echo get_authadillo_template_part(array('type' => 'front', 'name' => 'logout-button')) . $the_content;
	}	
}