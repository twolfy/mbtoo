<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: current_user_api.php,v 1.33 2007-10-24 22:30:59 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'filter_api.php' );

	### Current User API ###

	# Wrappers around the User API that pass in the logged-in user for you

	/**
	 * Returns the access level of the current user in the current project
	 * 	 
	 * @return access level code
	 * @access public	 
	 */
	function current_user_get_access_level() {
		return user_get_access_level( auth_get_current_user_id(),
										helper_get_current_project() );
	}

	/**
	 * Returns the number of open issues that are assigned to the current user
	 * in the current project.
	 * 	 	 
	 * @return Number of issues assigned to current user that are still open.
	 * @access public	 
	 */
	function current_user_get_assigned_open_bug_count() {
		return user_get_assigned_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}

	/**
	 * Returns the number of open reported bugs by the current user in
	 * the current project
	 * 	 	 
	 * @return Number of issues reported by current user that are still open.
	 * @access public	 
	 */
	function current_user_get_reported_open_bug_count() {
		return user_get_reported_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}

	/**
	 * Returns the specified field of the currently logged in user
	 *
	 * @param field_name  Name of user property as in the table definition.	 
	 * @return Get the value of the specified field for current user.
	 * @access public	 
	 */
	function current_user_get_field( $p_field_name ) {
		return user_get_field( auth_get_current_user_id(),
								$p_field_name );
	}

	/**
	 * Returns the specified field of the currently logged in user
	 *
	 * @param pref_name	Name of user preference as in the preferences table 
	 *					definition.	 
	 * @return Get the value of the specified preference for current user.
	 * @access public	 
	 */
	function current_user_get_pref( $p_pref_name ) {
		return user_pref_get_pref( auth_get_current_user_id(), $p_pref_name );
	}

	/**
	 * Sets the specified preference for the current logged in user.
	 * 
	 * @param pref_name		The name of the preference as in the preferences table.
	 * @param pref_value	The preference new value.
	 * @access public	 
	 */	 	 	 	 
	function current_user_set_pref( $p_pref_name, $p_pref_value ) {
		return user_pref_set_pref( auth_get_current_user_id(), $p_pref_name, $p_pref_value );
	}

	/**
	 * Return the specified field of the currently logged in user
	 * 
	 * @param project_id	The new default project id.
	 * @access public	 
	 */	 	 	 	 
	function current_user_set_default_project( $p_project_id ) {
		return user_set_default_project( auth_get_current_user_id(), $p_project_id );
	}

	/**
	 * Returns an array of projects that are accessible to the current logged in
	 * user.
	 * 	 
	 * @param show_disabled	Include disabled projects.
	 * @return an array of accessible project ids.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_get_accessible_projects( $p_show_disabled = false ) {
		return user_get_accessible_projects( auth_get_current_user_id(), $p_show_disabled );
	}

	/**
	 * Returns an array of subprojects of the specified project to which the
	 * currently logged in user has access to.
	 *
	 * @param project_id	Parent project id.	 
	 * @param show_disabled	Include disabled projects.
	 * @return an array of accessible sub-project ids.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_get_accessible_subprojects( $p_project_id, $p_show_disabled = false ) {
		return user_get_accessible_subprojects( auth_get_current_user_id(), $p_project_id, $p_show_disabled );
	}

	/**
	 * Returns an array of subprojects of the specified project to which the
	 * currently logged in user has access, including subprojects of subprojects
	 *
	 * @param project_id	Parent project id.	 
	 * @return an array of accessible sub-project ids.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_get_all_accessible_subprojects( $p_project_id ) {
		return user_get_all_accessible_subprojects( auth_get_current_user_id(), $p_project_id );
	}

	/**
	 * Returns true if the currently logged in user is has a role of administrator
	 * or higher, false otherwise
	 *
	 * @return true: administrator; false: otherwise.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_is_administrator() {
		return user_is_administrator( auth_get_current_user_id() );
	}

	/**
	 * Returns true if the currently logged in user protected, false otherwise
	 *
	 * @return true: user protected; false: otherwise.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_is_protected() {
		return user_is_protected( auth_get_current_user_id() );
	}

	/**
	 * Returns true if the currently user is the anonymous user
	 *
	 * @return true: user anonymous; false: otherwise.	 
	 * @access public	 
	 */	 	 	 	 
	function current_user_is_anonymous() {
		if ( auth_is_user_authenticated() ) {
			return ( ( ON == config_get( 'allow_anonymous_login' ) ) &&
			         ( current_user_get_field( 'username' ) == config_get( 'anonymous_account' ) ) );
		}
		else {
			return false;
		}
	}

	/**
	 * Triggers an ERROR if the current user account is protected
	 *
	 * @access public	 
	 */	 	 	 	 
	function current_user_ensure_unprotected() {
		user_ensure_unprotected( auth_get_current_user_id() );
	}

	/**
	 * Returns the issue filter parameters for the current user
	 *
	 * @return Active issue filter for current user.
	 * @access public	 
	 */	 	 	 	 
	function current_user_get_bug_filter( $p_project_id = null ) {
		$f_filter_string	= gpc_get_string( 'filter', '' );
		$t_view_all_cookie	= '';
		$t_cookie_detail	= '';
		$t_filter			= '';

		if ( !is_blank( $f_filter_string ) ) {
			if( is_numeric( $f_filter_string ) ) {
				$t_token = token_get_value( TOKEN_FILTER );
				if ( null != $t_token ) {
					$t_filter = unserialize( $t_token );
				}
			} else {
				$t_filter = unserialize( $f_filter_string );
			}
		} else if ( !filter_is_cookie_valid() ) {
			return false;
		} else {
			$t_user_id = auth_get_current_user_id();
			return user_get_bug_filter( $t_user_id, $p_project_id );
		}

		$t_filter = filter_ensure_valid_filter( $t_filter );
		return $t_filter;
	}
