<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_config_work_threshold_set.php,v 1.2 2005-03-19 16:29:42 thraxisp Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'email_api.php' );

	$t_redirect_url = 'manage_config_work_threshold_page.php';
	$t_project = helper_get_current_project();

	html_page_top1( lang_get( 'manage_threshold_config' ) );
	html_meta_redirect( $t_redirect_url );
	html_page_top2();

	$t_access = current_user_get_access_level();

	function set_capability_row( $p_threshold, $p_all_projects_only=false ) {
	    global $t_access, $t_project;
	    
	    if ( ( $t_access >= config_get_access( $p_threshold ) )
		          && ( ( ALL_PROJECTS == $t_project ) || ! $p_all_projects_only ) ) {
	        $f_threshold = gpc_get( 'flag_thres_' . $p_threshold );
	        $f_access = gpc_get_int( 'access_' . $p_threshold );
            # @@debug @@ echo "<br />for $p_threshold "; var_dump($f_threshold, $f_access); echo '<br />';	       
		    $t_access_levels = explode_enum_string( config_get( 'access_levels_enum_string' ) );
		    
		    $t_lower_threshold = ANYBODY;
		    $t_array_threshold = array();
		
		    foreach( $t_access_levels as $t_access_level ) {
			    $t_entry_array = explode_enum_arr( $t_access_level );
		        $t_set = $t_entry_array[0];
		        if ( in_array( $t_set, $f_threshold ) ) {
		            if ( ANYBODY == $t_lower_threshold ) {
		                $t_lower_threshold = $t_set;
		            }
		            $t_array_threshold[] = $t_set;
		        } else {
		            if ( ANYBODY <> $t_lower_threshold ) {
		                $t_lower_threshold = -1;
		            }
		        }
            # @@debug @@ var_dump($t_set, $t_lower_threshold, $t_array_threshold); echo '<br />';
            }
            if ( -1 == $t_lower_threshold ) {
		        config_set( $p_threshold, $t_array_threshold, NO_USER, $t_project, $f_access );
		    } else {
		        config_set( $p_threshold, $t_lower_threshold, NO_USER, $t_project, $f_access );
		    } 
		}
	}

	function set_capability_boolean( $p_threshold ) {
	    global $t_access, $t_project;
	    
	    if ( ( $t_access >= config_get_access( $p_threshold ) )
		          && ( ( ALL_PROJECTS == $t_project ) || ! $p_all_projects_only ) ) {
	        $f_flag = gpc_get( 'flag_' . $p_threshold, OFF );
	        $f_access = gpc_get_int( 'access_' . $p_threshold );
            # @@debug @@ echo "<br />for $p_threshold "; var_dump($f_flag, $f_access); echo '<br />';	       
		    
		    config_set( $p_threshold, $f_flag, NO_USER, $t_project, $f_access );
		}
	}

	function set_capability_enum( $p_threshold ) {
	    global $t_access, $t_project;
	    
	    if ( ( $t_access >= config_get_access( $p_threshold ) )
		          && ( ( ALL_PROJECTS == $t_project ) || ! $p_all_projects_only ) ) {
	        $f_flag = gpc_get( 'flag_' . $p_threshold );
	        $f_access = gpc_get_int( 'access_' . $p_threshold );
            # @@debug @@ echo "<br />for $p_threshold "; var_dump($f_flag, $f_access); echo '<br />';	       

		    config_set( $p_threshold, $f_flag, NO_USER, $t_project, $f_access );
		}
	}


	# Issues
	set_capability_row( 'report_bug_threshold' );
    set_capability_enum( 'bug_submit_status' );
	set_capability_row( 'update_bug_threshold' );
	set_capability_boolean( 'allow_close_immediately' );
    set_capability_boolean( 'allow_reporter_close' );
	set_capability_row( 'monitor_bug_threshold' );
	set_capability_row( 'handle_bug_threshold' );
 	set_capability_row( 'update_bug_assign_threshold' );
	set_capability_row( 'move_bug_threshold', true );
	set_capability_row( 'delete_bug_threshold' );
	set_capability_row( 'reopen_bug_threshold' );
    set_capability_boolean( 'allow_reporter_reopen' );
    set_capability_enum( 'bug_reopen_status' );
    set_capability_enum( 'bug_reopen_resolution' );
    set_capability_enum( 'bug_resolved_status_threshold' );
    set_capability_enum( 'bug_readonly_status_threshold' );
	set_capability_row( 'private_bug_threshold' );
	set_capability_row( 'update_readonly_bug_threshold' );
	set_capability_row( 'update_bug_status_threshold' );
	set_capability_row( 'set_view_status_threshold' );
	set_capability_row( 'change_view_status_threshold' );
	set_capability_row( 'show_monitor_list_threshold' );
    set_capability_boolean( 'auto_set_status_to_assigned' );
    set_capability_enum( 'bug_assigned_status', 'status' );
    set_capability_boolean( 'limit_reporters', true );

	# Notes
	set_capability_row( 'add_bugnote_threshold' );
	set_capability_row( 'update_bugnote_threshold' );
    set_capability_boolean( 'bugnote_allow_user_edit_delete' );
	set_capability_row( 'delete_bugnote_threshold' );
	set_capability_row( 'private_bugnote_threshold' );


	# Others
	set_capability_row( 'view_changelog_threshold' );
	set_capability_row( 'view_handler_threshold' );
	set_capability_row( 'view_history_threshold' );
	set_capability_row( 'bug_reminder_threshold' );

?>

<br />
<div align="center">
<?php
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>