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
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'relationship_api.php' );
	require_once( $t_core_path.'last_visited_api.php' );
	require_once( $t_core_path.'tag_api.php' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_history		= gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	bug_ensure_exists( $f_bug_id );

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	/**
	 * Tony Wolf - 2009-04-24
	 * @TODO please declare $g_project_override out-side of if, so it would be known outside the if-scope!
	 */
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( SIMPLE_ONLY == config_get( 'show_view' ) ) {
		print_header_redirect ( 'bug_view_page.php?bug_id=' . $f_bug_id );
	}

	compress_enable();

	html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );

	print_recently_visited();

	$t_access_level_needed = config_get( 'view_history_threshold' );
	$t_can_view_history = access_has_bug_level( $t_access_level_needed, $f_bug_id );

	$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );
?>

<br />
<table class="width100" cellspacing="1">


<tr>

	<!-- Title -->
	<td class="form-title" colspan="<?php echo $t_bugslist ? '3' : '4' ?>">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>

		<!-- Jump to Bugnotes -->
		<span class="small"><?php print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) ) ?></span>

		<!-- Send Bug Reminder -->
	<?php
		if ( !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
			  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id ) ) {
	?>
		<span class="small">
			<?php print_bracket_link( 'bug_reminder_page.php?bug_id='.$f_bug_id, lang_get( 'bug_reminder' ) ) ?>
	<?php
		}

		if ( wiki_enabled() ) {
			print_bracket_link( 'wiki.php?id='.$f_bug_id, lang_get( 'wiki' ) );
		}

		$t_links = event_signal( 'EVENT_MENU_ISSUE', $f_bug_id );

		foreach ( $t_links as $t_plugin => $t_hooks ) {
			foreach( $t_hooks as $t_hook ) {
				foreach( $t_hook as $t_label => $t_href ) {
					echo '&nbsp;';
					print_bracket_link( $t_href, $t_label );
				}
			}
		}
	?>
		</span>
	</td>

	<!-- prev/next links -->
	<?php if( $t_bugslist ) { ?>
	<td class="center"><span class="small">
		<?php
			$t_bugslist = explode( ',', $t_bugslist );
			$t_index = array_search( $f_bug_id, $t_bugslist );
			if( false !== $t_index ) {
				if( isset( $t_bugslist[$t_index-1] ) ) print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index-1], '&lt;&lt;' );
				if( isset( $t_bugslist[$t_index+1] ) ) print_bracket_link( 'bug_view_advanced_page.php?bug_id='.$t_bugslist[$t_index+1], '&gt;&gt;' );
			}
		?>
	</span></td>
	<?php } ?>

	<!-- Links -->
	<td class="right" colspan="2">

		<!-- Simple View (if enabled) -->
	<?php if ( BOTH == config_get( 'show_view' ) ) { ?>
			<span class="small"><?php print_bracket_link( 'bug_view_page.php?bug_id=' . $f_bug_id, lang_get( 'view_simple_link' ) ) ?></span>
	<?php } ?>

	<?php if ( $t_can_view_history ) { ?>
		<!-- History -->
		<span class="small"><?php print_bracket_link( 'bug_view_advanced_page.php?bug_id=' . $f_bug_id . '&amp;history=1#history', lang_get( 'bug_history' ) ) ?></span>
	<?php } ?>

		<!-- Print Bug -->
		<span class="small"><?php print_bracket_link( 'print_bug_page.php?bug_id=' . $f_bug_id, lang_get( 'print' ) ) ?></span>

	</td>

</tr>


<!-- Labels -->
<tr class="row-category">
	<td width="15%">
		<?php echo lang_get( 'id' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'severity' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'reproducibility' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'date_submitted' ) ?>
	</td>
	<td width="15%">
		<?php echo lang_get( 'last_update' ) ?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Bug ID -->
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>

	<!-- Category -->
	<td>
		<?php echo string_display( category_full_name( $t_bug->category_id ) );	?>
	</td>

	<!-- Severity -->
	<td>
		<?php echo get_enum_element( 'severity', $t_bug->severity ) ?>
	</td>

	<!-- Reproducibility -->
	<td>
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility ) ?>
	</td>

	<!-- Date Submitted -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>

	<!-- Date Updated -->
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>

</tr>


<!-- spacer -->
<tr class="spacer">
	<td colspan="6"></td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Reporter -->
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<?php print_user_with_subject( $t_bug->reporter_id, $f_bug_id ) ?>
	</td>

	<!-- View Status -->
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_bug->view_state ) ?>
	</td>

	<!-- Due Date -->
	<?php if ( access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id ) ) { ?>
	<td class="category">
		<?php echo lang_get( 'due_date' ) ?>
	</td>
	<?php
	if ( bug_is_overdue( $f_bug_id ) ) {
		print "<td class=\"overdue\">";
	} else {
		print "<td>";
	}
	?>
		<?php
			if ( !date_is_null( $t_bug->due_date ) ) {
				print_date( config_get( 'short_date_format' ), $t_bug->due_date ); }
			?>
	</td>
	<?php } else { ?>
		<!-- spacer -->
		<td colspan="2">&nbsp;</td>
	<?php
	}
	?>

</tr>


<!-- Handler -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<?php
			if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id ) ) {
				print_user_with_subject( $t_bug->handler_id, $f_bug_id );
			}
		?>
	</td>
</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Priority -->
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'priority', $t_bug->priority ) ?>
	</td>

	<!-- Resolution -->
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>

	<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
	<!-- Platform -->
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<?php echo $t_bug->platform ?>
	</td>
	<?php } else {?>
		<td colspan="2"></td>
	<?php } ?>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Status -->
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>

	<td colspan="2">&nbsp;</td>

	<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
	<!-- Operating System -->
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os ?>
	</td>
	<?php } else {?>
		<td colspan="2"></td>
	<?php } ?>

</tr>


<tr <?php echo helper_alternate_class() ?>>

	<!-- Projection -->
	<td class="category">
		<?php echo lang_get( 'projection' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'projection', $t_bug->projection ) ?>
	</td>

	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
	<!-- OS Version -->
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os_build ?>
	</td>
	<?php } else {?>
		<td colspan="2"></td>
	<?php } ?>

</tr>

<?php
	$t_show_eta = config_get( 'enable_eta' );
	$t_show_build = config_get( 'enable_product_build' );

	$t_show_version = ( ON == config_get( 'show_product_version' ) )
		|| ( ( AUTO == config_get( 'show_product_version' ) )
			&& ( count( version_get_all_rows( $t_bug->project_id ) ) > 0 ) );
?>

<?php if ( $t_show_eta || $t_show_version || $t_show_build ) { ?>
<tr <?php echo helper_alternate_class() ?>>

	<!-- ETA -->
		<?php
			if ( $t_show_eta ) {
		?>
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'eta', $t_bug->eta ) ?>
	</td>
		<?php
			} else {
		?>
	<td colspan="2"></td>
		<?php
			}
		?>

	<!-- fixed in version -->
		<?php
			if ( $t_show_version ) {
		?>
	<td class="category">
		<?php echo lang_get( 'fixed_in_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->fixed_in_version ?>
	</td>
		<?php
			} else {
		?>
	<td colspan="2"></td>
		<?php
			}
		?>
	<!-- Product Version or Product Build, if version is suppressed -->
		<?php
			if ( $t_show_version ) {
		?>
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->version ?>
	</td>
		<?php
			} else if ( $t_show_build )	{
		?>
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<?php echo $t_bug->build ?>
	</td>
		<?php
			} else {
		?>
	<td colspan="2"></td>
		<?php
			}
		?>

</tr>

<?php
	if( $t_show_version ) {
?>
<tr <?php echo helper_alternate_class() ?>>

<?php
	if ( access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id ) ) {
?>
	<!-- spacer -->
	<td colspan="2">&nbsp;</td>

	<!-- Target Version -->
	<td class="category">
		<?php echo lang_get( 'target_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->target_version ?>
	</td>
<?php
	} else {
?>
	<!-- spacer -->
	<td colspan="4">&nbsp;</td>
<?php
	}
?>

	<!-- Product Build -->
		<?php
			if ( $t_show_build ) {
		?>
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<?php echo $t_bug->build?>
	</td>
		<?php
			} else {
		?>
	<td colspan="2"></td>
		<?php
			}
		?>

</tr>
<?php
	}
}
?>


<?php event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $f_bug_id, true ) ); ?>

<!-- spacer -->
<tr class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<?php echo bug_format_summary( $f_bug_id, SUMMARY_FIELD ) ?>
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->description ?>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->steps_to_reproduce ?>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>

<!-- Tagging -->
<?php if ( access_has_global_level( config_get( 'tag_view_threshold' ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo lang_get( 'tags' ) ?></td>
	<td colspan="5">
<?php
	tag_display_attached( $f_bug_id );
?>
	</td>
</tr>
<?php } # has tag_view access ?>

<?php if ( access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo lang_get( 'tag_attach_long' ) ?></td>
	<td colspan="5">
<?php
	print_tag_attach_form( $f_bug_id );
?>
	</td>
</tr>
<?php } # has tag attach access ?>


<!-- spacer -->
<tr class="spacer">
	<td colspan="6"></td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;
		} # has read access

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );
?>
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo string_display( lang_get_defaulted( $t_def['name'] ) ) ?>
		</td>
		<td colspan="5">
		<?php print_custom_field_value( $t_def, $t_id, $f_bug_id ); ?>
		</td>
	</tr>
<?php
	} # foreach
?>

<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr class="spacer">
	<td colspan="6"></td>
</tr>
<?php } # custom fields found ?>


<!-- Attachments -->
<?php
	$t_show_attachments = ( $t_bug->reporter_id == auth_get_current_user_id() ) || access_has_bug_level( config_get( 'view_attachments_threshold' ), $f_bug_id );

	if ( $t_show_attachments ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<a name="attachments" id="attachments" />
		<?php echo lang_get( 'attached_files' ) ?>
	</td>
	<td colspan="5">
		<?php print_bug_attachments_list( $f_bug_id ); ?>
	</td>
</tr>
<?php
	}
?>

<!-- Buttons -->
<tr align="center">
	<td align="center" colspan="6">
<?php
	html_buttons_view_bug_page( $f_bug_id );
?>
	</td>
</tr>
</table>

<?php
	$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

	# User list sponsoring the bug
	include( $t_mantis_dir . 'bug_sponsorship_list_view_inc.php' );

	# Bug Relationships
	relationship_view_box ( $f_bug_id );

	# File upload box
	if ( !bug_is_readonly( $f_bug_id ) ) {
		include( $t_mantis_dir . 'bug_file_upload_inc.php' );
	}

	# User list monitoring the bug
	include( $t_mantis_dir . 'bug_monitor_list_view_inc.php' );

	# Bugnotes and "Add Note" box
	if ( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
		include( $t_mantis_dir . 'bugnote_view_inc.php' );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	} else {
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
		include( $t_mantis_dir . 'bugnote_view_inc.php' );
	}

	# Allow plugins to display stuff after notes
	event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );

	# History
	if ( $f_history ) {
		include( $t_mantis_dir . 'history_inc.php' );
	}

	html_page_bottom( __FILE__ );

	last_visited_issue( $f_bug_id );
