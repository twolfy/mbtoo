<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# Changes applied to 0.18 database

	# --------------------------------------------------------
	# $Id: 1_00_inc.php,v 1.6 2005-07-02 00:56:04 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require ( 'db_table_names_inc.php' );

	$upgrades = array();

	$upgrades[] = new SQLUpgrade(
			'config-key1',
			'make mantis_config_table keys not null',
			"ALTER TABLE $t_config_table CHANGE project_id project_id INT NOT NULL DEFAULT '0'"
		);

	$upgrades[] = new SQLUpgrade(
			'config-key2',
			'make mantis_config_table keys not null',
			"ALTER TABLE $t_config_table CHANGE user_id user_id INT NOT NULL DEFAULT '0'"
		);

	$upgrades[] = new SQLUpgrade(
			'configdb-pk',
			'Add mantis_config_table primary key',
			"ALTER TABLE $t_config_table
			    ADD PRIMARY KEY (config_id, project_id, user_id)"
		);

	$upgrades[] = new SQLUpgrade(
			'note_bug_id_index',
			'Add index on bug_id in bugnotes table',
			"ALTER TABLE $t_bugnote_table ADD INDEX ( bug_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_child_index',
			'Add index on child_id in project heirarchy table',
			"ALTER TABLE $t_project_hierarchy_table ADD INDEX ( child_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'bug_status_index',
			'Add index on status in bug table',
			"ALTER TABLE $t_bug_table ADD INDEX ( status )"
		);

	$upgrades[] = new SQLUpgrade(
			'bug_project_index',
			'Add index on project_id in bug table',
			"ALTER TABLE $t_bug_table ADD INDEX ( project_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'note_updated_index',
			'Add index on last_modified in bugnotes table',
			"ALTER TABLE $t_bugnote_table ADD INDEX ( last_modified )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_vs_index',
			'Add index on view_state in project table',
			"ALTER TABLE $t_project_table ADD INDEX ( view_state )"
		);

	$upgrades[] = new SQLUpgrade(
			'project_uid_index',
			'Add index on user_id in project_user table',
			"ALTER TABLE $t_project_user_list_table ADD INDEX ( user_id )"
		);

	$upgrades[] = new SQLUpgrade(
			'user_enabled_index',
			'Add index on enabled in user table',
			"ALTER TABLE $t_user_table ADD INDEX ( enabled )"
		);

	$upgrades[] = new SQLUpgrade(
			'user_access_index',
			'Add index on access_level in user table',
			"ALTER TABLE $t_user_table ADD INDEX ( access_level )"
		);
		
	$upgrades[] = new SQLUpgrade(
			'cf_string_bug_index',
			'Add index on bug_id in custom_field_string table',
			"ALTER TABLE $t_custom_field_string_table ADD INDEX ( bug_id )"
		);
	
	# this line should be the last upgrade in a version. When it is set, the upgrader
	# assumed that all updates in this file have been applied
	
	# uncomment the following line before the final release when the installer ( schema.php )is 
	# sync'd with these incremantal updates
	#$upgrades[] = new ReleaseUpgrade( '1.0.0' );



	return $upgrades;
?>
