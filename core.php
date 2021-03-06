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
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	###########################################################################
	# INCLUDES
	###########################################################################

	$g_request_time = microtime(true);

	ob_start();

	# Include compatibility file before anything else
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'php_api.php' );

	# Check if MantisBT is down for maintenance
	#
	#   To make MantisBT 'offline' simply create a file called
	#   'mantis_offline.php' in the MantisBT root directory.
	#   Users are redirected to that file if it exists.
	#   If you have to test MantisBT while it's offline, add the
	#   parameter 'mbadmin=1' to the URL.
	#
	$t_mantis_offline = 'mantis_offline.php';
	if ( file_exists( $t_mantis_offline ) && !isset( $_GET['mbadmin'] ) ) {
		include( $t_mantis_offline );
		exit;
	}

	/**
	 * @todo split config and reintroduce the new config-style
	 */
	require_once( 'config/general.inc.php' );


	# Load constants and configuration files
  	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'constant_inc.php' );

	if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' ) ) {
		require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' );
	}

	$t_config_inc_found = false;

	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_defaults_inc.php' );
	# config_inc may not be present if this is a new install
	if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' ) ) {
		require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' );
		$t_config_inc_found = true;
	}

	# Allow an environment variable (defined in an Apache vhost for example)
	#  to specify a config file to load to override other local settings
	$t_local_config = getenv( 'MANTIS_CONFIG' );
	if ( $t_local_config && file_exists( $t_local_config ) ){
		require_once( $t_local_config );
		$t_config_inc_found = true;
	}

	/**
	 *  @todo may be this could be done via .htaccess
	 */
	$t_include_path = ini_get( 'include_path' );
	$t_include_path = $g_config['template']['dir_smarty'].':'.implode( ':', $g_fs_paths ).':'.$t_include_path;

	ini_set( 'include_path', $t_include_path );
	unset( $t_include_path );

	# Attempt to find the location of the core files.
	$t_core_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
	if (isset($GLOBALS['g_core_path']) && !isset( $HTTP_GET_VARS['g_core_path'] ) && !isset( $HTTP_POST_VARS['g_core_path'] ) && !isset( $HTTP_COOKIE_VARS['g_core_path'] ) ) {
		$t_core_path = $g_core_path;
	}

	$g_core_path = $t_core_path;

	# Define an autoload function to automatically load classes when referenced.
	function __autoload( $className ) {
		global $g_core_path;

		switch ( $className ) {
			case 'DisposableEmailChecker':
				require_once( $g_core_path . 'disposable' . DIRECTORY_SEPARATOR . 'disposable.php' );
				return;
			case 'PHPMailer':
				require_once( PHPMAILER_PATH . 'class.phpmailer.php' ); // phpmailer_path is defined in email api
				return;
		}

		$t_require_path = $g_core_path . 'classes' . DIRECTORY_SEPARATOR . $className . '.class.php';

		if ( file_exists( $t_require_path ) ) {
			require_once( $t_require_path );
			return;
		}

		$t_require_path = $g_core_path . 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $className . '.inc.php';

		if ( file_exists( $t_require_path ) ) {
			require_once( $t_require_path );
			return;
		}

	}

	if ( ($t_output = ob_get_contents()) != '') {
		echo 'Possible Whitespace/Error in Configuration File - Aborting. Output so far follows:<br />';
		echo var_dump($t_output);
		die;
	}

	require_once( $t_core_path.'compress_api.php' );

	# Before doing anything else, start output buffering so we don't prevent
	#  headers from being sent if there's a blank line in an included file
	ob_start( 'compress_handler' );

	if ( false === $t_config_inc_found ) {
		# if not found, redirect to the admin page to install the system
		# this needs to be long form and not replaced by is_page_name as that function isn't loaded yet
		if ( !( isset( $_SERVER['PHP_SELF'] ) && ( 0 < strpos( $_SERVER['PHP_SELF'], 'admin' ) ) ) ) {
			if ( OFF == $g_use_iis ) {
				header( 'Status: 302' );
			}
			header( 'Content-Type: text/html' );

			if ( ON == $g_use_iis ) {
				header( "Refresh: 0;url=admin/install.php" );
			} else {
				header( "Location: admin/install.php" );
			}

			exit; # additional output can cause problems so let's just stop output here
		}
	}

	# Load rest of core in separate directory.

	require_once( $t_core_path.'config_api.php' );
	require_once( $t_core_path.'logging_api.php' );

	# load utility functions used by everything else
	require_once( $t_core_path.'utility_api.php' );

	# Load internationalization functions (needed before database_api, in case database connection fails)
	require_once( $t_core_path.'lang_api.php' );

	# error functions should be loaded to allow database to print errors
	require_once( $t_core_path.'error_api.php' );
	require_once( $t_core_path.'helper_api.php' );

	# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
	# OPENED ANYWHERE ELSE.
	require_once( $t_core_path.'database_api.php' );

	# PHP Sessions
	require_once( $t_core_path.'session_api.php' );

	# Initialize Event System
	require_once( $t_core_path.'event_api.php' );
	require_once( $t_core_path.'events_inc.php' );

	# Plugin initialization
	require_once( $t_core_path.'plugin_api.php' );
	if ( !defined( 'PLUGINS_DISABLED' ) ) {
		plugin_init_installed();
	}

	# Authentication and user setup
	require_once( $t_core_path.'authentication_api.php' );
	require_once( $t_core_path.'project_api.php' );
	require_once( $t_core_path.'project_hierarchy_api.php' );
	require_once( $t_core_path.'user_api.php' );
	require_once( $t_core_path.'access_api.php' );

	# Wiki Integration
	require_once( $t_core_path.'wiki_api.php' );

	# Display API's
	require_once( $t_core_path.'html_api.php' );
	require_once( $t_core_path.'gpc_api.php' );
	require_once( $t_core_path.'form_api.php' );
	require_once( $t_core_path.'print_api.php' );
	require_once( $t_core_path.'collapse_api.php' );
	if ( !defined( 'MANTIS_INSTALLER' ) ) {
		collapse_cache_token();
	}

	// custom functions (in main directory)
	/** @todo Move all such files to core/ */
	require_once( $t_core_path . 'custom_function_api.php' );
	$t_overrides = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'custom_functions_inc.php';
	if ( file_exists( $t_overrides ) ) {
		require_once( $t_overrides );
	}

	// seed random number generator
	list( $usec, $sec ) = explode( ' ', microtime() );
	mt_srand( $sec*$usec );

	// Basic browser detection
	$t_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'none';

	$t_browser_name = 'Normal';
	if ( strpos( $t_user_agent, 'MSIE' ) ) {
		$t_browser_name = 'IE';
	}

	// Headers to prevent caching
	//  with option to bypass if running from script
	global $g_bypass_headers, $g_allow_browser_cache;
	if ( !isset( $g_bypass_headers ) && !headers_sent() ) {

		if ( isset( $g_allow_browser_cache ) && ON == $g_allow_browser_cache ) {
			switch ( $t_browser_name ) {
			case 'IE':
				header( 'Cache-Control: private, proxy-revalidate' );
				break;
			default:
				header( 'Cache-Control: private, must-revalidate' );
				break;
			}

		} else {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		}

		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

		// send user-defined headers
		foreach( config_get( 'custom_headers' ) as $t_header ) {
			header( $t_header );
		}
	}

	// push push default language to speed calls to lang_get
	if ( !isset( $g_skip_lang_load ) )
		lang_push( lang_get_default() );

	if ( !isset( $g_bypass_headers ) && !headers_sent() ) {
		header( 'Content-type: text/html;charset=utf-8' );
	}

	# okay, let's init our template-system (an extended smarty-object)
	require_once('Output.lib.php');
	$g_output = new Output();

	/**
	 * We will activate html-headers on default for now.
	 * @todo decide to send headers depending if they are requested or needed
	 *
	 * This is in preperation for simple AJAX requests and will need additional attention later on.
	 */
	$g_output->mantis_enable_html_header();
	$g_output->mantis_enable_html_body_head();

	/**
	 * @todo only use this until we don't have one
	 */
	$g_output->mantis_set_module('login');

	/**
	 * @todo this has to be at the end of the whole process, but for migration we need it here!
	 */
	$g_output->display('index.tpl');

	// every page displays project dropdown box, so cache project information very early
	if ( db_is_connected() ) {
		project_cache_all();
	}
