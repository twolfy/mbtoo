 <?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Reset prefs to defaults then redirect to account_prefs_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### get user id
	$t_user_id = get_current_user_field( "id" );

	## reset to defaults
	$query = "UPDATE $g_mantis_user_pref_table
			SET default_project='0000000',
				advanced_report='$g_default_advanced_report',
				advanced_view='$g_default_advanced_view',
				advanced_update='$g_default_advanced_update',
				refresh_delay='$g_default_refresh_delay',
				redirect_delay='$g_default_redirect_delay',
				email_on_new='$g_default_email_on_new',
				email_on_assigned='$g_default_email_on_assigned',
				email_on_feedback='$g_default_email_on_feedback',
				email_on_resolved='$g_default_email_on_resolved',
				email_on_closed='$g_default_email_on_closed',
				email_on_reopened='$g_default_email_on_reopened',
				email_on_bugnote='$g_default_email_on_bugnote',
				email_on_status='$g_default_email_on_status',
				email_on_priority='$g_default_email_on_priority',
				language='$g_default_language'
			WHERE user_id='$t_user_id'";
	$result = db_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_account_prefs_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {					### SUCCESS
		PRINT "$s_prefs_reset_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_account_prefs_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>