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
 * @version $Id$
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * MantisBT Core API's
 */
require_once( '../core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path . 'graph_api.php' );

# Grab Data
# ---
$t_project_id = helper_get_current_project();

$data_category_arr = array();
$data_count_arr = array();
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_category_table = db_get_table( 'mantis_category_table' );
$query = "SELECT c.name AS name, COUNT(name) as count
		FROM $t_bug_table
		JOIN $t_category_table AS c
		WHERE project_id=" . db_param() . "
		GROUP BY name
		ORDER BY name";
$result = db_query_bound( $query, Array( $t_project_id ) );
$category_count = db_num_rows( $result );
$total = 0;
$longest_size = 0;
for( $i = 0;$i < $category_count;$i++ ) {
	$row = db_fetch_array( $result );
	extract( $row );

	$total += $count;
	$data_category_arr[] = $name;
	$data_count_arr[] = $count;

	if( strlen( $name ) > $longest_size ) {
		$longest_size = strlen( $name );
	}
}
$longest_size++;
for( $i = 0;$i < $category_count;$i++ ) {

	#echo $data_count_arr[$i];
	$percentage = number_format( $data_count_arr[$i] / $total * 100, 1 );

	#echo $percentage;
	$percentage_str = str_pad( $percentage, 5, ' ', STR_PAD_LEFT );
	$data_category_arr[$i] = str_pad( $data_category_arr[$i], $longest_size );
	$data_category_arr[$i] = $data_category_arr[$i] . $percentage_str;
	if( $percentage < 1 ) {
		$data_count_arr[$i] = 0;
	}
}

$proj_name = project_get_field( $t_project_id, 'name' );

# Setup Graph
# ---
$graph = new PieGraph( 800, 600 );
$graph->SetShadow();

# Set A title for the plot
$graph->title->Set( "Category Distribution Graph: $proj_name" );
$graph->title->SetFont( FF_FONT2, FS_BOLD );

# Create graph
$p1 = new PiePlot( $data_count_arr );
$p1->SetLegends( $data_category_arr );
$p1->SetSize( 260 );
$p1->SetCenter( 0.35 );
$p1->SetSliceColors( $g_color_arr );
$p1->SetStartAngle( - 90 );

$graph->Add( $p1 );

$graph->Stroke();
