<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: my_view_inc_resolved.php,v 1.2 2004-06-28 13:04:32 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	$c_filter_resolved = array(
		'_version'		=> 'v5',			'show_category'		=> Array ( '0' => 'any' ),
		'show_severity'		=> Array ( '0' => 'any' ),	'show_status'		=> Array ( '0' => 80 ),
		'per_page'		=> '50',			'highlight_changed'	=> '6',
		'hide_closed'		=> 'on',			'reporter_id'		=> Array ( '0' => 'any' ),
		'handler_id'		=> Array ( '0' => 'any' ),	'sort'			=> 'last_updated',
		'dir'			=> 'DESC',			'start_month'		=> '',
		'start_day'		=> '',				'start_year'		=> '',
		'end_month'		=> '',				'end_day'		=> '',
		'end_year'		=> '',				'search'		=> '',
		'hide_resolved'		=> '',				'and_not_assigned'	=> '',
		'show_resolution'	=> Array ( '0' => 'any' ),	'show_build'		=> Array ( '0' => 'any' ),
		'show_version'		=> Array ( '0' => 'any' ),	'do_filter_by_date'	=> '',
		'custom_fields'		=> Array ( ),			'_view_type'            => 'simple',
		'hide_status'           => Array ( '0' => 90 )
	);

	$rows = filter_get_bug_rows ( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter_resolved );

	$box_title = lang_get( 'my_view_title_resolved' );
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
?>