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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	form_security_validate('manage_custom_field_proj_add');

	auth_reauthenticate();

	$f_field_id = gpc_get_int( 'field_id' );
	$f_project_id = gpc_get_int_array( 'project_id', array() );
	$f_sequence	= gpc_get_int( 'sequence' );

	$t_manage_project_threshold = config_get( 'manage_project_threshold' );

	foreach ( $f_project_id as $t_proj_id ) {
		if ( access_has_project_level( $t_manage_project_threshold, $t_proj_id ) ) {
			if ( !custom_field_is_linked( $f_field_id, $t_proj_id ) ) {
				custom_field_link( $f_field_id, $t_proj_id );
			}

			custom_field_set_sequence( $f_field_id, $t_proj_id, $f_sequence );
		}
	}

	form_security_purge('manage_custom_field_proj_add');

	print_header_redirect( 'manage_custom_field_edit_page.php?field_id=' . $f_field_id );
?>
