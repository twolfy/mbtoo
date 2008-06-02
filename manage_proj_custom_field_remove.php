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
	# $Id$
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'custom_field_api.php' );

	auth_reauthenticate();

	$f_field_id = gpc_get_int( 'field_id' );
	$f_project_id = gpc_get_int( 'project_id' );
	$f_return = gpc_get_string( 'return', '' );

	# We should check both since we are in the project section and an
	# admin might raise the first threshold and not realize they need
	# to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id );

	$t_definition = custom_field_get_definition( $f_field_id );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'confirm_custom_field_unlinking' ) .
		'<br/>' . lang_get( 'custom_field' ) . ': ' . string_attribute( $t_definition['name'] ),
		lang_get( 'field_remove_button' ) );

	if ( $f_return == 'custom_field' ) {
		$t_redirect_url = 'manage_custom_field_edit_page.php?field_id=' . $f_field_id;
	} else {
		$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
	}

	form_security_validate( 'manage_proj_custom_field_remove' );
	custom_field_unlink( $f_field_id, $f_project_id );

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
