<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: logout_page.php,v 1.12 2003-01-18 02:14:12 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Removes all the cookies and then redirect to $g_logout_redirect_page
?>
<?php require_once( 'core.php' ); ?>
<?php
	auth_logout();

	print_header_redirect( config_get( 'logout_redirect_page' ) );
?>