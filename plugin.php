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

require_once( 'core.php' );
$t_plugin_path = config_get( 'plugin_path' );

$f_page= gpc_get_string( 'page' );
$t_matches = array();

if ( ! preg_match( '/^([a-zA-Z0-9_-]*)\/([a-zA-Z0-9_-]*)/', $f_page, $t_matches ) ) {
		trigger_error( ERROR_GENERIC, ERROR );
}

$t_basename = $t_matches[1];
$t_action = $t_matches[2];

plugin_ensure_registered( $t_basename );

$t_page = $t_plugin_path.$t_basename.DIRECTORY_SEPARATOR.
		'pages'.DIRECTORY_SEPARATOR.$t_action.'.php';

if ( !is_file( $t_page ) ) {
		trigger_error( ERROR_PLUGIN_PAGE_NOT_FOUND, ERROR );
}

include( $t_page );

