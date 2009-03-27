<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package MantisBT
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
 /**
  * MantisBT Core API's
  */
require_once( 'core.php' );
$t_core_path = config_get( 'core_path' );
require_once ( $t_core_path . 'html_api.php' );
require_once ( $t_core_path . 'form_api.php' );
require_once( $t_core_path . 'tag_api.php' );

form_security_validate( 'tag_create' );

$f_tag_name = gpc_get_string( 'name' );
$f_tag_description = gpc_get_string( 'description' );

$t_tag_user = auth_get_current_user_id();

if ( !is_null( $f_tag_name )) {
	$t_tags = tag_parse_string( $f_tag_name );
	foreach ( $t_tags as $t_tag_row ) {
		if ( -1 == $t_tag_row['id'] ) {
			tag_create( $t_tag_row['name'], $t_tag_user, $f_tag_description );
		}
	}
}

form_security_purge( 'tag_create' );
print_successful_redirect( 'manage_tags_page.php' );

