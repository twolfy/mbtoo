<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: main_page.php,v 1.46 2004-02-08 08:00:06 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'news_inc.php' );
?>
<?php
	access_ensure_project_level( VIEWER );

	$f_offset = gpc_get_int( 'offset', 0 );

?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php
	if ( !current_user_is_anonymous() ) {
		echo '<div class="quick-summary-left">';
		echo lang_get( 'open_and_assigned_to_me' ) . ':';
		echo '<a href="view_all_set.php?type=1&amp;reporter_id=any&amp;show_status=any&amp;show_severity=any&amp;show_category=any&amp;handler_id=' .  auth_get_current_user_id() . '&amp;hide_closed=on&amp;hide_resolved=on">' . current_user_get_assigned_open_bug_count() . '</a>';
		echo '</div>';

		echo '<div class="quick-summary-right">';
		echo lang_get( 'open_and_reported_to_me' ) . ':';
		echo '<a href="view_all_set.php?type=1&amp;reporter_id=' . auth_get_current_user_id() . '&amp;show_status=any&amp;show_severity=any&amp;show_category=any&amp;handler_id=any&amp;hide_closed=on&amp;hide_resolved=on">' . current_user_get_reported_open_bug_count() . '</a>';
		echo '</div>';

		echo '<div class="quick-summary-left">';
		echo lang_get( 'last_visit' ) . ': ';
		echo print_date( config_get( 'normal_date_format' ), strtotime(current_user_get_field( 'last_visit' )));
		echo '</div>';
	}
?>

<br />
<br />

<?php
	$c_offset = db_prepare_int( $f_offset );

	$t_project_id = helper_get_current_project();

	# get news count (project plus sitewide posts)
	$total_news_count = news_get_count( $t_project_id );

	$t_news_table			= config_get( 'mantis_news_table' );
	$t_news_view_limit		= config_get( 'news_view_limit' );
	$t_news_view_limit_days	= config_get( 'news_view_limit_days' );

	switch ( config_get( 'news_limit_method' ) ) {
		case 0 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $t_news_table
					WHERE project_id='$t_project_id' OR project_id=" . ALL_PROJECTS . "
					ORDER BY announcement DESC, id DESC
					LIMIT $c_offset, $t_news_view_limit";
			break;
		case 1 :
			# Select the news posts
			$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
					FROM $t_news_table
					WHERE ( project_id='$t_project_id' OR project_id=" . ALL_PROJECTS . " ) AND
						(TO_DAYS(NOW()) - TO_DAYS(date_posted) < '$t_news_view_limit_days')
					ORDER BY announcement DESC, id DESC";
			break;
	} # end switch
	$result = db_query( $query );
	$news_count = db_num_rows( $result );

	# Loop through results
	for ( $i = 0; $i < $news_count; $i++ ) {
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		# only show VS_PRIVATE posts to configured threshold and above
		if ( ( VS_PRIVATE == $v_view_state ) &&
			 !access_has_project_level( config_get( 'private_news_threshold' ) ) ) {
			continue;
		}

		print_news_entry( $v_headline, $v_body, $v_poster_id, $v_view_state, $v_announcement, $v_date_posted );
		echo '<br />';
	}  # end for loop
?>

<?php # Print NEXT and PREV links if necessary ?>
<div align="center">
<?php
	print_bracket_link( 'news_list_page.php', lang_get( 'archives' ) );
	$f_offset_next = $f_offset + $t_news_view_limit;
	$f_offset_prev = $f_offset - $t_news_view_limit;

	if ( $f_offset_prev >= 0) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_prev, lang_get( 'newer_news_link' ) );
	}
	if ( $news_count == $t_news_view_limit ) {
		print_bracket_link( 'main_page.php?offset=' . $f_offset_next, lang_get( 'older_news_link' ) );
	}
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
