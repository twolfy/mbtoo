<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: jump_to_bug.php,v 1.14 2003-01-24 14:59:34 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# Redirect to the appropriate viewing page for the bug
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'bug_api.php' );
	require_once( $g_core_path . 'project_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );
	project_access_check( $f_bug_id );
	bug_ensure_exists( $f_bug_id );

	# Determine which view page to redirect back to.
	print_header_redirect_view( $f_bug_id );
?>
