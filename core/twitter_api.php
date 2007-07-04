<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: twitter_api.php,v 1.3 2007-07-04 06:09:28 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'config_api.php' );

	$g_twitter_enabled = null;

	# --------------------
	# Checks if twitter is used for the current installation.
	# returns true for enabled, false otherwise.
	function twitter_enabled() {
		global $g_twitter_enabled;

		if ( null === $g_twitter_enabled ) {
			$g_twitter_enabled = !is_blank( config_get( 'twitter_username' ) );
		}
		
		if ( $g_twitter_enabled && !function_exists( 'curl_init' ) ) {
			trigger_error( ERROR_TWITTER_NO_CURL_EXT, ERROR );
		}

		return $g_twitter_enabled;
	}
	
	# --------------------
	# Posts a twitter update when a bug is resolved.
	# @param $p_bug_id The bug id that was resolved.
	function twitter_issue_resolved( $p_bug_id ) {
		if ( !twitter_enabled() ) {
			return true;
		}
		
		$t_bug = bug_get( $p_bug_id, false );

		# Do not twitter except fixed issues
		if ( $t_bug->resolution != FIXED ) {
			return true;
		}

		# Do not twitter private bugs.
		if ( $t_bug->view_state != VS_PUBLIC ) {
			return true;
		}
		
		# Do not twitter bugs belonging to private projects.
		if ( VS_PRIVATE == project_get_field( $t_bug->project_id, 'view_state' ) ) {
			return true;
		}

		$c_bug_id = db_prepare_int( $p_bug_id );

		if ( is_blank( $t_bug->fixed_in_version ) ) {
			$t_message = sprintf( 
							lang_get( 'twitter_resolved_no_version' ), 
							$c_bug_id, 
							$t_bug->category,
							$t_bug->summary, 
							user_get_name( $t_bug->handler_id ) );
		} else {
			$t_message = sprintf(
							lang_get( 'twitter_resolved' ), 
							$c_bug_id, 
							$t_bug->category,
							$t_bug->summary, 
							user_get_name( $t_bug->handler_id ),
							$t_bug->fixed_in_version );
		}

		return twitter_update( $t_message );
	}

	# --------------------
	# Posts an update to twitter
	# @param $p_message  The message to post.
	function twitter_update( $p_message ) {
		if ( !twitter_enabled() ) {
			return true;
		}

		if ( is_blank( $p_message ) ) {
			return true;
		}
		
		$c_message = db_prepare_string( $p_message );

		// Set username and password
		$t_username = config_get( 'twitter_username' );
		$t_password = config_get( 'twitter_password' );

		// The twitter API address
		$t_url = 'http://twitter.com/statuses/update.xml';

		// Set up and execute the curl process
		$t_curl = curl_init();

		curl_setopt( $t_curl, CURLOPT_URL, $t_url );
		curl_setopt( $t_curl, CURLOPT_CONNECTTIMEOUT, 2 );
		curl_setopt( $t_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $t_curl, CURLOPT_POST, 1);
		curl_setopt( $t_curl, CURLOPT_POSTFIELDS, "status=$c_message" );
		curl_setopt( $t_curl, CURLOPT_USERPWD, "$t_username:$t_password" );

		$t_buffer = curl_exec( $t_curl );

		curl_close( $t_curl );
		
		return !is_blank( $t_buffer );
	}
?>
