<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: relationship_api.php,v 1.10 2004-07-12 04:37:43 int2str Exp $
	# --------------------------------------------------------

	### Relationship API ###

	# MASC RELATIONSHIP

	# --------------------------------------------------------
	# Author: Marcello Scat� marcello@marcelloscata.com
	#
	# --------------------------------------------------------
	# RELATIONSHIP DEFINITIONS
	# * Child/parent relationship:
	#    the child bug is generated by the parent bug or is directly linked with the parent with the following meaning
	#    the child bug has to be resolved before resolving the parent bug (the child bug "blocks" the parent bug)
	#    example: bug A is child bug of bug B. It means: A blocks B and B is blocked by A
	# * General relationship:
	#    two bugs related each other without any hierarchy dependance
	#    bugs A and B are related
	# * Duplicates:
	#    it's used to mark a bug as duplicate of an other bug already stored in the database
	#    bug A is marked as duplicate of B. It means: A duplicates B, B has duplicates
	#
	# Relations are always visible in the email body
	# --------------------------------------------------------
	# ADD NEW RELATIONSHIP
	# - Permission: user can update the source bug and at least view the destination bug
	# - Action recorded in the history of both the bugs
	# - Email notification sent to the users of both the bugs based based on the 'updated' bug notify type.
	# --------------------------------------------------------
	# DELETE RELATIONSHIP
	# - Permission: user can update the source bug and at least view the destination bug
	# - Action recorded in the history of both the bugs
	# - Email notification sent to the users of both the bugs based based on the 'updated' bug notify type.
	# --------------------------------------------------------
	# RESOLVE/CLOSE BUGS WITH BLOCKING CHILD BUGS STILL OPEN
	# Just a warning is print out on the form when an user attempts to resolve or close a bug with
	# related bugs in relation BUG_DEPENDANT still not resolved.
	# Anyway the user can force the resolving/closing action.
	# --------------------------------------------------------
	# EMAIL NOTIFICATION TO PARENT BUGS WHEN CHILDREN BUGS ARE RESOLVED/CLOSED
	# Every time a child bug is resolved or closed, an email notification is sent directly to all the handlers
	# of the parent bugs. The notification is sent to bugs not already marked as resolved or closed.
	# --------------------------------------------------------
	# ADD CHILD
	# This function gives the opportunity to generate a child bug. In details the function:
	# - create a new bug with the same basic information of the parent bug (plus the custom fields)
	# - copy all the attachment of the parent bug to the child
	# - not copy history, bugnotes, monitoring users
	# - set a relationship between parent and child
	# --------------------------------------------------------

	class BugRelationshipData {
		var $id;
		var $src_bug_id;
		var $dest_bug_id;
		var $type;
	}

	# --------------------
	function relationship_add( $p_src_bug_id, $p_dest_bug_id, $p_relationship_type ) {
		$c_src_bug_id = db_prepare_int( $p_src_bug_id );
		$c_dest_bug_id = db_prepare_int( $p_dest_bug_id );
		$c_relationship_type = db_prepare_int( $p_relationship_type );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "INSERT INTO $t_mantis_bug_relationship_table
				( source_bug_id, destination_bug_id, relationship_type )
				VALUES
				( '$c_src_bug_id', '$c_dest_bug_id', '$c_relationship_type' )";
		$result = db_query( $query );
		$t_relationship = db_fetch_array( $result );

		$t_bug_relationship_data = new BugRelationshipData;
		$t_bug_relationship_data->id = $t_relationship['id'];
		$t_bug_relationship_data->src_bug_id = $t_relationship['source_bug_id'];
		$t_bug_relationship_data->dest_bug_id = $t_relationship['destination_bug_id'];
		$t_bug_relationship_data->type = $t_relationship['relationship_type'];

		return $t_bug_relationship_data;
	}

	# --------------------
	function relationship_update( $p_relation_id, $p_src_bug_id, $p_dest_bug_id, $p_relationship_type ) {
		$c_relation_id = db_prepare_int( $p_relation_id );
		$c_src_bug_id = db_prepare_int( $p_src_bug_id );
		$c_dest_bug_id = db_prepare_int( $p_dest_bug_id );
		$c_relationship_type = db_prepare_int( $p_relationship_type );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "UPDATE $t_mantis_bug_relationship_table
				SET source_bug_id='$c_src_bug_id',
					destination_bug_id='$c_dest_bug_id',
					relationship_type='$c_relationship_type'
				WHERE id='$c_relation_id'";
		$result = db_query( $query );
		$t_relationship = db_fetch_array( $result );

		$t_bug_relationship_data = new BugRelationshipData;
		$t_bug_relationship_data->id = $t_relationship['id'];
		$t_bug_relationship_data->src_bug_id = $t_relationship['source_bug_id'];
		$t_bug_relationship_data->dest_bug_id = $t_relationship['destination_bug_id'];
		$t_bug_relationship_data->type = $t_relationship['relationship_type'];

		return $t_bug_relationship_data;
	}

	# --------------------
	function relationship_delete( $p_relation_id ) {
		$c_relation_id = db_prepare_int( $p_relation_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "DELETE FROM $t_mantis_bug_relationship_table
				WHERE id='$c_relation_id'";
		$result = db_query( $query );
	}

	# --------------------
	# Deletes all the relationships related to a specific bug (both source and destination)
	function relationship_delete_all( $p_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "DELETE FROM $t_mantis_bug_relationship_table
				WHERE source_bug_id='$c_bug_id' OR
				destination_bug_id='$c_bug_id'";
		$result = db_query( $query );
	}

	# --------------------
	# copy all the relationships related to a specific bug to a new bug
	function relationship_copy_all( $p_bug_id, $p_new_bug_id ) {
		$c_bug_id = db_prepare_int( $p_bug_id );
		$c_new_bug_id = db_prepare_int( $p_new_bug_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$t_relationship = relationship_get_all_src( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			relationship_add($p_new_bug_id, $t_relationship[$i]->dest_bug_id, $t_relationship[$i]->type);
		}

		$t_relationship = relationship_get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			relationship_add($t_relationship[$i]->src_bug_id, $p_new_bug_id, $t_relationship[$i]->type);
		}

		return;
	}

	# --------------------
	function relationship_get( $p_relation_id ) {
		$c_relation_id = db_prepare_int( $p_relation_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE id='$c_relation_id'";
		$result = db_query( $query, 1 );
		$t_relationship = db_fetch_array( $result );

		$t_bug_relationship_data = new BugRelationshipData;
		$t_bug_relationship_data->id = $t_relationship['id'];
		$t_bug_relationship_data->src_bug_id = $t_relationship['source_bug_id'];
		$t_bug_relationship_data->dest_bug_id = $t_relationship['destination_bug_id'];
		$t_bug_relationship_data->type = $t_relationship['relationship_type'];

		return $t_bug_relationship_data;
	}

	# --------------------
	function relationship_get_all_src( $p_src_bug_id ) {
		$c_src_bug_id = db_prepare_int( $p_src_bug_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE source_bug_id='$c_src_bug_id'
				ORDER BY relationship_type, id";
		$result = db_query( $query );

		$t_bug_relationship_data = array( new BugRelationshipData );
		$t_relationship_count = db_num_rows( $result );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_bug_relationship_data[$i]->id = $row['id'];
			$t_bug_relationship_data[$i]->src_bug_id = $row['source_bug_id'];
			$t_bug_relationship_data[$i]->dest_bug_id = $row['destination_bug_id'];
			$t_bug_relationship_data[$i]->type = $row['relationship_type'];
		}
		unset( $t_bug_relationship_data[$t_relationship_count] );

		return $t_bug_relationship_data;
	}

	# --------------------
	function relationship_get_all_dest( $p_dest_bug_id ) {
		$c_dest_bug_id = db_prepare_int( $p_dest_bug_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE destination_bug_id='$c_dest_bug_id'
				ORDER BY relationship_type, id";
		$result = db_query( $query );

		$t_bug_relationship_data = array( new BugRelationshipData );
		$t_relationship_count = db_num_rows( $result );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$row = db_fetch_array( $result );
			$t_bug_relationship_data[$i]->id = $row['id'];
			$t_bug_relationship_data[$i]->src_bug_id = $row['source_bug_id'];
			$t_bug_relationship_data[$i]->dest_bug_id = $row['destination_bug_id'];
			$t_bug_relationship_data[$i]->type = $row['relationship_type'];
		}
		unset( $t_bug_relationship_data[$t_relationship_count] );

		return $t_bug_relationship_data;
	}

	# --------------------
	# check if there is a relationship between two bugs
	function relationship_exists( $p_src_bug_id, $p_dest_bug_id ) {
		$c_src_bug_id = db_prepare_int( $p_src_bug_id );
		$c_dest_bug_id = db_prepare_int( $p_dest_bug_id );

		$t_mantis_bug_relationship_table = config_get( 'mantis_bug_relationship_table' );

		$t_query = "SELECT *
				FROM $t_mantis_bug_relationship_table
				WHERE
				(source_bug_id='$c_src_bug_id'
				AND destination_bug_id='$c_dest_bug_id')
				OR
				(source_bug_id='$c_dest_bug_id'
				AND destination_bug_id='$c_src_bug_id')";
		$t_result = db_query( $t_query );

		# TRUE if the bugs are already related
		return (db_num_rows( $t_result ) > 0);
	}

	# --------------------
	# retrieve the linked bug id of the relationship: provide src -> return dest; provide dest -> return src
	function relationship_get_linked_bug_id( $p_relationship_id, $p_bug_id ) {

		$t_bug_relationship_data = relationship_get( $p_relationship_id );

		if ( $t_bug_relationship_data->src_bug_id == $p_bug_id ) {
			return $t_bug_relationship_data->dest_bug_id;
		}

		if ( $t_bug_relationship_data->dest_bug_id == $p_bug_id ) {
			return $t_bug_relationship_data->src_bug_id;
		}

		trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
	}

	# --------------------
	# get class description of a relationship (source side)
	function relationship_get_description_src_side( $p_relationship_type ) {
		switch ( $p_relationship_type ) {
			case BUG_DUPLICATE:
				return lang_get( 'duplicate_of' );
				break;
			case BUG_RELATED:
				return lang_get( 'related_to' ) ;
				break;
			case BUG_DEPENDANT:
				return lang_get( 'dependant_on' ) ;
				break;
			default:
				trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# get class description of a relationship (destination side)
	function relationship_get_description_dest_side( $p_relationship_type ) {
		switch ( $p_relationship_type ) {
			case BUG_DUPLICATE:
				return lang_get( 'has_duplicate' ) ;
				break;
			case BUG_RELATED:
				return lang_get( 'related_to' ) ;
				break;
			case BUG_DEPENDANT:
				return lang_get( 'blocks' ) ;
				break;
			default:
				trigger_error( ERROR_RELATIONSHIP_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# return false if there are child bugs not resolved/closed
	# N.B. we don't check if the parent bug is read-only. This is because the answer of this function is indepent from
	# the state of the parent bug itself.
	function relationship_can_resolve_bug( $p_bug_id ) {

		# retrieve all the relationships in which the bug is the source bug
		$t_relationship = relationship_get_all_src( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		if ( $t_relationship_count == 0 ) {
			return true;
		}

		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			# verify if each bug in relation BUG_DEPENDANT is already marked as resolved
			if ( $t_relationship[$i]->type == BUG_DEPENDANT ) {
				$t_dest_bug_id = $t_relationship[$i]->dest_bug_id;
				$t_status = bug_get_field( $t_dest_bug_id, 'status' );
				if ( $t_status < config_get( 'bug_resolved_status_threshold' ) ) {
					# the bug is NOT marked as resolved/closed
					return false;
				}
			}
		}

		return true;
	}

	# --------------------
	# return formatted string with all the details on the requested relationship
	function relationship_get_details( $p_bug_id, $p_relationship, $p_html = false, $p_html_preview = false, $p_user_id = null ) {

		if ( $p_user_id === null ) {
			$p_user_id = auth_get_current_user_id();
		}

		if ( $p_bug_id == $p_relationship->src_bug_id ) {
			# root bug is in the src side, related bug in the dest side
			$t_related_bug_id = $p_relationship->dest_bug_id;
			$t_relationship_descr = relationship_get_description_src_side( $p_relationship->type );
		}
		else {
			# root bug is in the dest side, related bug in the src side
			$t_related_bug_id = $p_relationship->src_bug_id;
			$t_relationship_descr = relationship_get_description_dest_side( $p_relationship->type );
		}

		if ( bug_exists( $t_related_bug_id ) ) {
			# related bug existing...
			if ( access_has_bug_level( VIEWER, $t_related_bug_id ) ) {
				# user can access to the related bug at least as a viewer

				# get the information from the related bug and prepare the link
				$t_bug = bug_prepare_display( bug_get( $t_related_bug_id, true ) );
				$t_status = string_attribute( get_enum_element( 'status', $t_bug->status ) );

				$t_relationship_info_html = $t_relationship_descr . '</td>';
				if ( $p_html_preview == false ) {
					$t_relationship_info_html .= '<td><a href="' . string_get_bug_view_url( $t_related_bug_id ) . '">' . bug_format_id( $t_related_bug_id ) . '</a></td>';
					$t_relationship_info_html .= '<td bgcolor="' . get_status_color( $t_bug->status ) . '">' . $t_status . '&nbsp;</td><td>';
				}
				else {
					$t_relationship_info_html .= '<td>' . bug_format_id( $t_related_bug_id ) . '</td>';
					$t_relationship_info_html .= '<td>' . $t_status . '&nbsp;</td><td>';
				}

				$t_relationship_info_text = str_pad( $t_relationship_descr,25);
				$t_relationship_info_text .= str_pad( bug_format_id( $t_related_bug_id ),8 );
				$t_relationship_info_text .= str_pad( $t_status,15 );

				# get the handler name of the related bug
				if ( $t_bug->handler_id > 0 )  {
					$t_relationship_info_html .= string_attribute( user_get_name(  $t_bug->handler_id ) );
				}

				# add summary
				$t_relationship_info_html .= '&nbsp;</td><td><i>' . string_attribute( $t_bug->summary ) . '</i>';
			}
			else {
				# no viewer access to the related bug
				$t_relationship_info_html = bug_format_id( $t_related_bug_id ) . '</td><td>&nbsp;</td><td>&nbsp;</td><td>';
				$t_relationship_info_text = str_pad( bug_format_id( $t_related_bug_id ),8 );
			}
		}
		else {
			# related bug not found...
			$t_relationship_info_html = bug_format_id( $t_related_bug_id ) . '</td><td>&nbsp;</td><td>&nbsp;</td><td>';
			$t_relationship_info_text = str_pad( bug_format_id( $t_related_bug_id ),8 );
		}

		# add delete link if bug not read only and user has access level
		if ( !bug_is_readonly( $p_bug_id ) && !current_user_is_anonymous() && ( $p_html_preview == false ) ) {
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
				$t_relationship_info_html .= " [<a class=\"small\" href=\"bug_relationship_delete.php?bug_id=$p_bug_id&rel_id=$p_relationship->id\">" . lang_get('delete_link') . '</a>]';
			}
		}

		$t_relationship_info_text .= "\n";

		if ( $p_html_preview == false ) {
			$t_relationship_info_html = '<tr class="row-2"><td>' . $t_relationship_info_html . '&nbsp;</td></tr>';
		}
		else {
			$t_relationship_info_html = '<tr class="print"><td>' . $t_relationship_info_html . '&nbsp;</td></tr>';
		}

		if ( $p_html == true ) {
			return $t_relationship_info_html;
		}
		else {
			return $t_relationship_info_text;
		}

	}

	# --------------------
	# print ALL the RELATIONSHIPS OF A SPECIFIC BUG
	function relationship_get_summary_html( $p_bug_id ) {
		$t_summary = '';

		$t_relationship = relationship_get_all_src( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], true, false );
		}

		$t_relationship = relationship_get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], true, false );
		}

		if ( relationship_can_resolve_bug( $p_bug_id ) == false ) {
			$t_summary .= '<tr class="row-2"><td colspan=5><b>' . lang_get( 'relationship_warning_blocking_bugs_not_resolved' ) . '</b></td></tr>';
		}

		$t_summary = '<table class="width100">' . $t_summary . '</table>';

		return $t_summary;
	}

	# --------------------
	# print ALL the RELATIONSHIPS OF A SPECIFIC BUG
	function relationship_get_summary_html_preview( $p_bug_id ) {
		$t_summary = '';

		$t_relationship = relationship_get_all_src( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], true, true );
		}

		$t_relationship = relationship_get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], true, true );
		}

		if ( relationship_can_resolve_bug( $p_bug_id ) == false ) {
			$t_summary .= '<tr class="print"><td colspan=5><b>' . lang_get( 'relationship_warning_blocking_bugs_not_resolved' ) . '</b></td></tr>';
		}

		$t_summary = '<table cellspacing=0 cellpadding=1 border=0>' . $t_summary . '</table>';

		return $t_summary;
	}

	# --------------------
	# print ALL the RELATIONSHIPS OF A SPECIFIC BUG in text format (used by email_api.php
	function relationship_get_summary_text( $p_bug_id ) {
		$t_email_separator1 = config_get( 'email_separator1' );
		$t_email_separator2 = config_get( 'email_separator2' );

		$t_summary = "";

		$t_relationship = relationship_get_all_src( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], false );
		}

		$t_relationship = relationship_get_all_dest( $p_bug_id );
		$t_relationship_count = count( $t_relationship );
		for ( $i = 0 ; $i < $t_relationship_count ; $i++ ) {
			$t_summary .= relationship_get_details ( $p_bug_id, $t_relationship[$i], false );
		}

		if ($t_summary != "") {
			$t_summary =
				$t_email_separator1 . "\n" .
				str_pad( lang_get( 'bug_relationships' ),25 ) .
				str_pad( lang_get( 'id' ),8 ) .
				str_pad( lang_get( 'status' ),15 ) . "\n" .
				$t_email_separator2 . "\n" . $t_summary;
		}

		return $t_summary;
	}

 	# --------------------
 	# print HTML relationship form
	function relationship_view_box( $p_bug_id ) {
?>
<br/>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="relationships_closed">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<a href="" onClick="ToggleDiv( 'relationships', g_div_relationships ); return false;"
			><img border="0" src="images/plus.png" alt="+" /></a>
		<?php PRINT lang_get( 'bug_relationships' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="relationships_open">
<table class="width100" cellspacing="1">
<tr class="row-2">
	<td width="15%" class="form-title">
		<a href="" onClick="ToggleDiv( 'relationships', g_div_relationships ); return false;"
			><img border="0" src="images/minus.png" alt="-" /></a>
		<?php PRINT lang_get( 'bug_relationships' ) ?>
	</td>
	<td><?php PRINT relationship_get_summary_html( $p_bug_id ) ?></td>
</tr>
<?php
		# bug not read-only and user authenticated
		if ( !bug_is_readonly( $p_bug_id ) && !current_user_is_anonymous() ) {

			# user access level at least updater
			if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
?>
<tr class="row-1">
	<td class="category"><?php PRINT lang_get( 'add_new_relationship' ) ?></td>
	<td><?php PRINT lang_get( 'this_bug' ) ?>
		<form method="POST" action="bug_relationship_add.php">
		<input type="hidden" name="src_bug_id" value="<?php PRINT $p_bug_id ?>" size="4" />
		<select name="rel_type">
		<option value="<?php PRINT BUG_RELATED ?>"><?php PRINT lang_get( 'related_to' ) ?></option>
		<option value="<?php PRINT BUG_DEPENDANT ?>"><?php PRINT lang_get( 'dependant_on' ) ?></option>
		<option value="<?php PRINT BUG_BLOCKS ?>"><?php PRINT lang_get( 'blocks' ) ?></option>
		<option value="<?php PRINT BUG_DUPLICATE ?>"><?php PRINT lang_get( 'duplicate_of' ) ?></option>
		<option value="<?php PRINT BUG_HAS_DUPLICATE ?>"><?php PRINT lang_get( 'has_duplicate' ) ?></option>
		</select>
		<input type="text" name="dest_bug_id" value="" maxlength="7" />
		<input type="submit" name="add_relationship" class="button" value="<?php PRINT lang_get( 'add_new_relationship_button' ) ?>" />
		</form>
	</td></tr>
<?php
			}
		}
?>
</table>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
	SetDiv( "relationships", g_div_relationships );
</script>
<?php } ?>

<?php
	}

	# MASC RELATIONSHIP
?>
