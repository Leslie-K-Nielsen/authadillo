<?php 

if (!defined('AUTHADILLO_ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Authadillo
{
	public $error_message;
	public $authentication;
		
	function __construct($auth_obj)		
	{
		$this->authentication = $auth_obj;

		$this->error_message = false;

		/* Initialize the plugin */
		add_action('init', array($this, 'InitActions'));
	}
	
	function InitActions()
	{
		/* Do something with the data entered */
		add_action('save_post', array($this, 'SavePostData'));
		
		/* Define the custom box */
		add_action('add_meta_boxes', array($this, 'AddCustomBox'));

		/* Add Actions */
		add_action( 'the_post', array($this, 'MyThePostAction'));

		/* Check for Login or Signup */
		if(isset($_POST['authadillo_form_request_type']))
		{
			if($_POST['authadillo_form_request_type'] == 'login')
			{
				$this->authentication->SignIn();
			}
			else if($_POST['authadillo_form_request_type'] == 'signup')
			{
				$this->RegisterNewMember();
			}
		}
	}	

	function ActivatePlugin()
	{
		global $wpdb;
		
		$wpdb->hide_errors();
		
		$collate = '';
	
		if ($wpdb->has_cap( 'collation' ) ) 
		{
			$collate = $wpdb->get_charset_collate();
		}
		
		$new_table = "CREATE TABLE {$wpdb->prefix}authadillo_members (
		  id int(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
		  id_hash tinytext NOT NULL,
		  username tinytext NOT NULL,
		  password tinytext NOT NULL,
		  firstname tinytext NOT NULL,
		  lastname tinytext NOT NULL
		) $collate;";	

		$wpdb->query($new_table);
	}

	function DeactivatePlugin()
	{
		global $wpdb;
		
		$wpdb->hide_errors();
		
		//Remove any tables or assests created
		$drop_table = "DROP TABLE {$wpdb->prefix}authadillo_members";
		$wpdb->query($drop_table);
		error_log($drop_table);
		
	}

	/* When the post is saved, saves our custom data */
	function SavePostData($post_id) 
	{
		// verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['authadillo_noncename'], 'authadillo_authadillo_field_nonce' ) )
			return;
		
		if ( isset($_POST['authadillo_protected_page']) && $_POST['authadillo_protected_page'] != "" ){
				update_post_meta( $post_id, 'authadillo_protected_page', $_POST['authadillo_protected_page'] );
		} 
	}

	/* Adds a box to the main column on the Post and Page edit screens */
	function AddCustomBox() 
	{
		add_meta_box( 
			'authadillo_sectionid',
			'AuthAdillo Protected Page?',
			array($this, 'InnerCustomBox'),
			'',
			'side',
			'high'
		);
	}

	/* Prints the box content */
	function InnerCustomBox($post)
	{
		// Use nonce for verification
		wp_nonce_field( 'authadillo_authadillo_field_nonce', 'authadillo_noncename' );

		// Get saved value, if none exists, "default" is selected
		$saved = get_post_meta( $post->ID, 'authadillo_protected_page', true);
		if( !$saved )
			$saved = 'default';

		$fields = array(
			'protected'       => __('Protected', 'authadillo'),
			'public'     => __('Public', 'authadillo'),
		);

		foreach($fields as $key => $label)
		{
			printf(
				'<input type="radio" name="authadillo_protected_page" value="%1$s" id="authadillo_protected_page[%1$s]" %3$s />'.
				'<label for="authadillo_protected_page[%1$s]"> %2$s ' .
				'</label><br>',
				esc_attr($key),
				esc_html($label),
				checked($saved, $key, false)
			);
		}
	}

	function MyThePostAction($post_object) 
	{
		$protected = get_post_meta( $post_object->ID, 'authadillo_protected_page', true);
		
		if($protected == 'protected')
		{
			if($this->authentication->GetAuthStatus())
			{
				//Good, they can view the content. Put the nav menu in the page so they can logout and access other controls				
				add_filter('the_content', array($this->authentication, 'GenerateNavMenu'));
			}
			else
			{
				//Show the login form
				add_filter('the_content', array($this->authentication, 'ForceLoginForm'));
			}
		}
	}

	function RegisterNewMember()
	{
		global $wpdb;

		if(isset($_POST) && !empty($_POST))
		{
			$is_valid = true;
			
			//Generate ID Hash
			$sql_nvp_values['id_hash'] = generate_key();
			
			foreach($_POST as $name => $value)
			{
				if($value != '')
				{				
					switch($name)
					{
						case 'firstname':												
						case 'lastname':						
								$value = $wpdb->_real_escape($value);
								$sql_nvp_values[$name] = $value;														
							break;	
						case 'password':
								//Hash Password. Save record to database and send registration confirmation
								$value = $wpdb->_real_escape($value);								
								$sql_nvp_values[$name] = password_hash($value, PASSWORD_DEFAULT);														
							break;
						case 'username':
								$value = $wpdb->_real_escape($value);
								$sql_nvp_values[$name] = $value;
							break;								
						default:
							break;					
					}
				}
				else
				{
					$is_valid = false;
				}				
			}
			
			if($is_valid)
			{
				$insert_statment = "INSERT INTO {$wpdb->prefix}authadillo_members ";
				$vals = ""; 
				$cols = "";

				foreach($sql_nvp_values as $name => $value) 
				{
					$cols.="$name, ";
					
					if(strtoupper($value) == 'NULL') 
					{	
						$vals.="NULL, ";
					}
					else
					{		
						$vals.= "'".$wpdb->_real_escape($value)."', ";
					}	
				}

				$insert_statment .= "(". rtrim($cols, ', ') .") VALUES (". rtrim($vals, ', ') .");";
				
				$result = $wpdb->query($insert_statment);				
				
				$_SESSION['authadillo_user_auth'] = $wpdb->insert_id.".".$sql_nvp_values['id_hash'];				
			}
		}

		if(!$is_valid)	
		{
			$this->error_message = 'Make sure you fill out all the fields.';		
		}
	}	
}

?>