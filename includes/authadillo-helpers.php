<?php

if (!defined('AUTHADILLO_ABSPATH')) {
    exit; // Exit if accessed directly.
}

function increq_file($path, $method = 'include') 
{	
	$include_file = AUTHADILLO_DIR.'/'.$path;
	
	if (!file_exists($include_file)) 
	{
		trigger_error("Looks like the file ".$include_file." does not exist and likely was not installed with the plugin. ", E_USER_WARNING);
	}

	if ('include' === $method) 
	{
		include($include_file);
	} 
	elseif ('include_once' === $method) 
	{
		include_once($include_file);
	} 
	elseif ('require' === $method) 
	{
		require($include_file);
	} 
	else 
	{
		require_once($include_file);
	}	
}

function get_authadillo_template_part($params)
{
	/*
		Params:
		fetch = fileget|include|include_once|require
		type = front|admin
		path = subdirectories in type
		name = template file name without extension
	*/
	
	$path = (isset($params['path']) && !empty($params['path'])) ? $params['path'] . '/' : "";
	$template_file = AUTHADILLO_ABSPATH . 'templates/' . $params['type'] . '/'. $path . $params['name']. '.php';

	if(isset($params['fetch']))
	{
		if(in_array($params['fetch'], array('include','include_once','require')))
		{
			$template_file = 'templates/' . $params['type'] . '/'. $path . $params['name']. '.php';

			increq_file($template_file, $params['fetch']); 
		}
		else
		{
			return file_get_contents($template_file);
		}
	}
	else
	{
		return file_get_contents($template_file);
	}

	
}

function authadillo_template_merges($array, $template, $custom_fields = array())
{
	$output = "";
	
	$idx = 0;	
	
	if(!empty($array))
	{
		foreach($array as $row_name => $row_val)
		{
			$idxlbl = "{{".$row_name."}}";
			
			if(isset($custom_fields) && in_array($row_name, $custom_fields))
			{
				$value = custom_parse($row_name, $row_val);
			}
			else
			{
				$value = $row_val;
			}
			
			$tmp_str = ($idx == 0) ? str_ireplace($idxlbl, $value, $template) : str_ireplace($idxlbl, $value, $tmp_str);
								
			$idx++;
		}	

		$output = $tmp_str;
	}
	
	return $output;
}

function custom_parse($name, $value)
{
	switch($name)
	{
		/*
		//Sample uses for potential custom values
		case 'available':
			return ($value) ? "Yes" : "No";
			break;	
		case 'product_type':
			return ($value == "monthly-subscription") ? "/month" : "";
			break;
		*/	
		default:
			break;
	}			
}

function P($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}	

function generate_key()
{
	$key_len = '20';
	$vowels = 'aeiouyAEIOU';
	$consonants = 'bcdfghjklmnpqrstvwxzBCDFGHJKLMNPRSTVWXZ234567890';
	$alt = time() % 2;
	
	for ($i = 0; $i < $key_len; $i++) 
	{
		if ($alt == 1) 
		{
			$keyvalue.= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} 
		else 
		{
			$keyvalue.= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
		
	return $keyvalue;
}