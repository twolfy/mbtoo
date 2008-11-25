<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package CoreAPI
 * @subpackage ProfileAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# ===================================
# Boolean queries and ensures
# ===================================
# ===================================
# Creation / Deletion / Updating
# ===================================

# Create a new profile for the user, return the ID of the new profile
function profile_create( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_platform = db_prepare_string( $p_platform );
	$c_os = db_prepare_string( $p_os );
	$c_os_build = db_prepare_string( $p_os_build );
	$c_description = db_prepare_string( $p_description );

	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# platform cannot be blank
	if( is_blank( $c_platform ) ) {
		error_parameters( lang_get( 'platform' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os cannot be blank
	if( is_blank( $c_os ) ) {
		error_parameters( lang_get( 'operating_system' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os_build cannot be blank
	if( is_blank( $c_os_build ) ) {
		error_parameters( lang_get( 'version' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	# Add profile
	$query = "INSERT INTO $t_user_profile_table
				    ( user_id, platform, os, os_build, description )
				  VALUES
				    ( " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . " )";
	db_query_bound( $query, Array( $c_user_id, $c_platform, $c_os, $c_os_build, $c_description ) );

	return db_insert_id( $t_user_profile_table );
}

# Delete a profile for the user
#
# Note that although profile IDs are currently globally unique, the existing
#  code included the user_id in the query and I have chosen to keep that for
#  this API as it hides the details of id implementation from users of the API
function profile_delete( $p_user_id, $p_profile_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_profile_id = db_prepare_int( $p_profile_id );

	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	# Delete the profile
	$query = "DELETE FROM $t_user_profile_table
				  WHERE id=" . db_param() . " AND user_id=" . db_param();
	db_query_bound( $query, Array( $c_profile_id, $c_user_id ) );

	# db_query errors on failure so:
	return true;
}

# Update a profile for the user
function profile_update( $p_user_id, $p_profile_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_profile_id = db_prepare_int( $p_profile_id );
	$c_platform = db_prepare_string( $p_platform );
	$c_os = db_prepare_string( $p_os );
	$c_os_build = db_prepare_string( $p_os_build );
	$c_description = db_prepare_string( $p_description );

	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# platform cannot be blank
	if( is_blank( $c_platform ) ) {
		error_parameters( lang_get( 'platform' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os cannot be blank
	if( is_blank( $c_os ) ) {
		error_parameters( lang_get( 'operating_system' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os_build cannot be blank
	if( is_blank( $c_os_build ) ) {
		error_parameters( lang_get( 'version' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	# Add item
	$query = "UPDATE $t_user_profile_table
				  SET platform=" . db_param() . ",
				  	  os=" . db_param() . ",
					  os_build=" . db_param() . ",
					  description=" . db_param() . "
				  WHERE id=" . db_param() . " AND user_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_platform, $c_os, $c_os_build, $c_description, $c_profile_id, $c_user_id ) );

	# db_query errors on failure so:
	return true;
}

# ===================================
# Data Access
# ===================================

# Return a profile row from the database
function profile_get_row( $p_user_id, $p_profile_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_profile_id = db_prepare_int( $p_profile_id );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE id=" . db_param() . " AND user_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_profile_id, $c_user_id ) );

	return db_fetch_array( $result );
}

# Return a profile row from the database
function profile_get_row_direct( $p_profile_id ) {
	$c_profile_id = db_prepare_int( $p_profile_id );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_profile_id ) );

	return db_fetch_array( $result );
}

# Return an array containing all rows for a given user
function profile_get_all_rows( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE user_id=" . db_param() . "
				  ORDER BY platform, os, os_build";
	$result = db_query_bound( $query, Array( $c_user_id ) );

	$t_rows = array();
	$t_row_count = db_num_rows( $result );

	for( $i = 0;$i < $t_row_count;$i++ ) {
		array_push( $t_rows, db_fetch_array( $result ) );
	}

	return $t_rows;
}

# Return an array containing all profiles for a given user,
# including global profiles
function profile_get_all_for_user( $p_user_id ) {
	if( ALL_USERS == $p_user_id ) {
		return profile_get_all_rows( ALL_USERS );
	} else {
		$t_profiles_array = array_merge( profile_get_all_rows( ALL_USERS ), profile_get_all_rows( $p_user_id ) );
		asort( $t_profiles_array );
		return $t_profiles_array;
	}
}

# Return an array of strings containing unique values for the specified field based
# on private and public profiles accessible to the specified user.
function profile_get_field_all_for_user( $p_field, $p_user_id = null ) {
	$c_user_id = ( $p_user_id === null ) ? auth_get_current_user_id() : db_prepare_int( $p_user_id );
	$c_field = db_prepare_string( $p_field );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	$query = "SELECT DISTINCT $c_field
				  FROM $t_user_profile_table
				  WHERE ( user_id='$c_user_id' ) OR ( user_id = '0' )
				  ORDER BY $c_field";
	$result = db_query( $query );

	$t_rows = array();

	$t_row_count = db_num_rows( $result );

	for( $i = 0;$i < $t_row_count;$i++ ) {
		$t_row = db_fetch_array( $result );
		array_push( $t_rows, $t_row[$c_field] );
	}

	return $t_rows;
}

# Return an array containing all profiles used in a given project
function profile_get_all_for_project( $p_project_id ) {
	$t_project_where = helper_project_specific_where( $p_project_id );

	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	# using up.* causes an SQL error on MS SQL since up.description is of type text
	$query = "SELECT DISTINCT(up.id), up.user_id, up.platform, up.os, up.os_build
				  FROM $t_user_profile_table up, $t_bug_table b
				  WHERE $t_project_where
				  AND up.id = b.profile_id";
	$result = db_query( $query );

	$t_rows = array();
	$t_row_count = db_num_rows( $result );

	for( $i = 0;$i < $t_row_count;$i++ ) {
		array_push( $t_rows, db_fetch_array( $result ) );
	}

	return $t_rows;
}

# Return an array containing all global profiles
function profile_get_global() {
	return profile_get_all_rows( ALL_USERS );
}

# Returns the default profile
function profile_get_default( $p_user_id ) {

	$c_user_id = db_prepare_int( $p_user_id );
	$t_mantis_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = "SELECT default_profile
			FROM $t_mantis_user_pref_table
			WHERE user_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_user_id ) );

	$t_default_profile = db_result( $result, 0, 0 );

	return $t_default_profile;
}

# Returns whether the specified profile is global
function profile_is_global( $p_profile_id ) {
	$t_row = profile_get_row( ALL_USERS, $p_profile_id );
	return( $t_row !== false );
}

# ===================================
# Data Modification
# ===================================
