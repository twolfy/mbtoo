<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: helper_api.php,v 1.27 2002-11-27 02:42:37 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Helper API
	###########################################################################

	# These are miscellaneous functions to help the package

	# --------------------
	# Calculates the CRC given bug id and calling file name (use __FILE__).
	# It uses a configuration variable as a seed.
	function helper_calc_crc ( $p_bug_id, $p_file ) {
		$t_crc_str = sprintf("%s%s%07d", config_get( 'admin_crypt_word' ), basename($p_file), (integer)$p_bug_id);
		return crc32($t_crc_str);
	}
	# --------------------
	# converts a 1 value to X
	# converts a 0 value to a space
	function trans_bool( $p_num ) {
		if ( 0 == $p_num ) {
			return '&nbsp;';
		} else {
			return 'X';
		}
	}
	# --------------------
	# alternate color function
	#  If no index is given, continue alternating based on the last index given
	function helper_alternate_colors( $p_index=null, $p_odd_color, $p_even_color ) {
		static $t_index = 1;

		if ( null !== $p_index ) {
			$t_index = $p_index;
		}

		if ( 1 == $t_index++ % 2 ) {
			return $p_odd_color;
		} else {
			return $p_even_color;
		}
	}
	# --------------------
	# alternate classes for table rows
	#  If no index is given, continue alternating based on the last index given
	function helper_alternate_class( $p_index=null, $p_odd_class="row-1", $p_even_class="row-2" ) {
		static $t_index = 1;
		
		if ( null !== $p_index ) {
			$t_index = $p_index;
		}

		if ( 1 == $t_index++ % 2 ) {
			return "class=\"$p_odd_class\"";
		} else {
			return "class=\"$p_even_class\"";
		}
	}
	# --------------------
	# get the color string for the given status
	function get_status_color( $p_status ) {
		$t_status_enum_string = config_get( 'status_enum_string' );
		$t_status_colors = config_get( 'status_colors' );
		$t_custom_status_slot = config_get( 'custom_status_slot' );
		$t_customize_attributes = config_get( 'customize_attributes' );
		

		# This code creates the appropriate variable name
		# then references that color variable
		# You could replace this with a bunch of if... then... else
		# statements

		if ($t_customize_attributes) {
			# custom colors : to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# insert attriutes for color displaying in viex_bug_page.php
			attribute_insert( 'status', $t_project_id, 'global' );
			attribute_insert( 'status', $t_project_id, 'str' ) ;
		}

		$t_color_str = 'closed';
		$t_arr = explode_enum_string( $t_status_enum_string );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_status ) {
				# now get the appropriate translation
				$t_color_str = $elem_arr[1];
				break;
			}
		}

		$t_color_variable_name = $t_color_str.'_color';
		if ( config_is_set( $t_color_variable_name ) ) {
			return config_get( $t_color_variable_name );
		} elseif ( isset ( $t_status_colors[$t_color_str] ) ) {
			return $t_status_colors[$t_color_str];
		} elseif ($t_customize_attributes) {   // custom attributes
				# if not found before, look into custom status colors
				$t_colors_arr = attribute_get_all('colors', $t_project_id);
				$t_offset = ( $p_status-( $t_custom_status_slot[0]+1 ) );
				if ( isset( $t_colors_arr[$t_offset ]) ) {
					return $t_colors_arr[$t_offset];
				}
		}
		return '#ffffff';
	}
	# --------------------
	# Get the string associated with the $p_enum value
	function get_enum_to_string( $p_enum_string, $p_num ) {
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			if ( $t_s[0] == $p_num ) {
				return $t_s[1];
			}
		}
		return '@null@';
	}
	# --------------------
	# Breaks up an enum string into num:value elements
	function explode_enum_string( $p_enum_string ) {
		return explode( ',', $p_enum_string );
	}
	# --------------------
	# Given one num:value pair it will return both in an array
	# num will be first (element 0) value second (element 1)
	function explode_enum_arr( $p_enum_elem ) {
		return explode( ':', $p_enum_elem );
	}
	# --------------------
	# Given a enum string and num, return the appropriate string
	function get_enum_element( $p_enum_name, $p_val ) {
		$config_var = config_get( $p_enum_name.'_enum_string' );
		$string_var = lang_get(  $p_enum_name.'_enum_string' );
		$t_customize_attributes = config_get( 'customize_attributes' );

		# custom attributes
		if ($t_customize_attributes) {
			# to be deleted when moving to manage_project_page.php
			$t_project_id = '0000000';

			# custom attributes insertion
			attribute_insert( $p_enum_name, $t_project_id, 'global' );
			attribute_insert( $p_enum_name, $t_project_id, 'str' ) ;
		}
		# use the global enum string to search
		$t_arr = explode_enum_string( $config_var );
		$t_arr_count = count( $t_arr );
		for ( $i=0;$i<$t_arr_count;$i++ ) {
			$elem_arr = explode_enum_arr( $t_arr[$i] );
			if ( $elem_arr[0] == $p_val ) {
				# now get the appropriate translation
				return get_enum_to_string( $string_var, $p_val );
			}
		}
		return '@null@';
	}
	# --------------------
	# Contributed by Peter Palmreuther
	function mime_encode( $p_string='' ) {
		$output = '';
		for ( $i=0; $i<strlen( $p_string ); $i++ ) {
			if (( ord( $p_string[$i] ) < 33 ) ||
				( ord( $p_string[$i] ) > 127 ) ||
				( eregi( "[\%\[\]\{\}\(\)]", $p_string[$i] ) )) {
				$output .= sprintf( '%%%02X', ord( $p_string[$i] ) );
			} else {
				$output .= $p_string[$i];
			}
		}
		return( $output );
	}
	# --------------------
	# This function checks to see if a variable is set
	# if it is not then it assigns the default value
	# otherwise it does nothing
	function check_varset( &$p_var, $p_default_value ) {
	     if ( !isset( $p_var ) ) {
	         $p_var = $p_default_value;
	     }
	}
	# --------------------
	# If $p_var and $p_val are euqal to each other then we echo SELECTED
	# This is used when we want to know if a variable indicated a certain
	# option element is selected
	function check_selected( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo ' selected="selected" ';
		}
	}
	# --------------------
	# If $p_var and $p_val are euqal to each other then we echo CHECKED
	# This is used when we want to know if a variable indicated a certain
	# element is checked
	function check_checked( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo ' checked="checked" ';
		}
	}
	# --------------------
	# Return the current project id as stored in a cookie
	#  If no cookie exists, the user's default project is returned
	function helper_get_current_project() {
		$t_cookie_name = config_get( 'project_cookie' );

		$t_project_id = gpc_get_cookie( $t_cookie_name, null );

		if ( null === $t_project_id ) {
			return (int)current_user_get_pref( 'default_project' );
		} else {
			return (int)$t_project_id;
		}
	}
	# --------------------
	# Add a trailing DIRECTORY_SEPARATOR to a string if it isn't present
	function helper_terminate_directory_path( $p_path ) {
		if ( $p_path && $p_path[strlen($p_path)-1] != DIRECTORY_SEPARATOR ) {
			$p_path = $p_path.DIRECTORY_SEPARATOR;
		}

		return $p_path;
	}
	# --------------------
	# Clear all known user preference cookies
	function helper_clear_pref_cookies() {
		gpc_clear_cookie( 'project_cookie' );
		gpc_clear_cookie( 'view_all_cookie' );
		gpc_clear_cookie( 'manage_cookie' );
	}
	# --------------------
	# Check whether the user has confirmed this action.
	#
	# If the user has not confirmed the action, generate a page which asks
	#  the user to confirm and then submits a form back to the current page
	#  with all the GET and POST data and an additional field called _confirmed
	#  to indicate that confirmation has been done.
	function helper_ensure_confirmed( $p_message, $p_button_label ) {
		if (true == gpc_get_bool( '_confirmed' ) ) {
			return true;
		}

		global $PHP_SELF;
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_POST, $_GET;
		}

		print_page_top1();
		print_page_top2();

		echo "<br />\n<div align=\"center\">\n";
		print_hr();
		echo "\n$p_message\n";

		echo '<form method="post" action="' . $PHP_SELF . "\">\n";

		print_hidden_inputs( gpc_strip_slashes( $_POST ) );
		print_hidden_inputs( gpc_strip_slashes( $_GET ) );

		echo "<input type=\"hidden\" name=\"_confirmed\" value=\"1\" />\n";
		echo '<br /><br /><input type="submit" value="' . $p_button_label . '" />';
		echo "\n</form>\n";

		print_hr();
		echo "</div>\n";
		print_page_bot1();
		exit;
	}
	# --------------------
	# Print a debug string by generating a notice
	function debug( $p_string ) {
		trigger_error( $p_string, NOTICE );
	}

	# --------------------
	# Return true if the parameter is an empty string or a string
	#  containing only whitespace, false otherwise
	function is_blank( $p_var ) {
		if ( strlen( trim( $p_var ) ) == 0 ) {
			return true;
		}
		return false;
	}
?>
