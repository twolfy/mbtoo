<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: upgrade.php,v 1.12 2005-07-17 10:06:01 prichards Exp $
	# --------------------------------------------------------
?>
<?php
	$g_skip_open_db = true;  # don't open the database in database_api.php
	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
	$g_error_send_page_header = false; # suppress page headers in the error handler

    # @@@ upgrade list moved to the bottom of upgrade_inc.php
    
	$f_advanced = gpc_get_bool( 'advanced', false );
?>
<html>
<head>
<title> Mantis Administration - Upgrade Installation </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			[ <a href="upgrade_list.php">Back to Upgrade List</a> ]
			[ <a href="upgrade.php">Refresh view</a> ]
			[ <a href="upgrade.php?advanced=<?php echo ( $f_advanced ? 0 : 1 ) ?>"><?php echo ( $f_advanced ? 'Simple' : 'Advanced' ) ?></a> ]
		</td>
		<td class="title">
			Upgrade Installation
		</td>
	</tr>
</table>
<br /><br />
<?php

	$result = @db_connect( config_get_global( 'dsn', false ), config_get_global( 'hostname' ), config_get_global( 'db_username' ), config_get_global( 'db_password' ), config_get_global( 'database_name' ) );
	if ( false == $result ) {
?>
<p>Opening connection to database [<?php echo config_get_global( 'database_name' ) ?>] on host [<?php echo config_get_global( 'hostname' ) ?>] with username [<?php echo config_get_global( 'db_username' ) ?>] failed.</p>
</body>
<?php
        exit();
	}
	
	if ( ! db_table_exists( config_get( 'mantis_upgrade_table' ) ) ) {
        # Create the upgrade table if it does not exist
        $query = "CREATE TABLE " . config_get( 'mantis_upgrade_table' ) .
				  "(upgrade_id char(20) NOT NULL,
				  description char(255) NOT NULL,
				  PRIMARY KEY (upgrade_id))";

        $result = db_query( $query );
    }

	# link the data structures and upgrade list
	require_once ( 'upgrade_inc.php' );
	
	$upgrade_set->process_post_data( $f_advanced );
?>
</body>
</html>
