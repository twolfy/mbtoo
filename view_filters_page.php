<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );

	auth_ensure_user_authenticated();

	compress_enable();

	html_page_top1();
	html_page_top2();
	
	if ( ON == config_get( 'use_javascript' ) ) {
		?>
		<body onload="SetInitialFocus();">
		
		<script language="Javascript">
		function SetInitialFocus() {
			<?php
			$t_target_field = gpc_get_string( 'target_field', '' );
			if ( $t_target_field ) {
				print "field_to_focus = \"$t_target_field\";";
			} else {
				print "field_to_focus = null;";
			}
			?>
			if ( field_to_focus ) {
				eval( "document.filters." + field_to_focus + ".focus()" );
			}
			
			SwitchDateFields();
		}
		
		function SwitchDateFields() {
		    // All fields need to be enabled to go back to the script
			document.filters.start_month.disabled = ! document.filters.do_filter_by_date.checked;
			document.filters.start_day.disabled = ! document.filters.do_filter_by_date.checked;
			document.filters.start_year.disabled = ! document.filters.do_filter_by_date.checked;
			document.filters.end_month.disabled = ! document.filters.do_filter_by_date.checked;
			document.filters.end_day.disabled = ! document.filters.do_filter_by_date.checked;
			document.filters.end_year.disabled = ! document.filters.do_filter_by_date.checked;
		
		    return true;
		}
		</script>

		<?php
	}
	
	$t_filter = current_user_get_bug_filter();
	$t_project_id = helper_get_current_project();

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];
	
	$t_current_user_access_level = current_user_get_access_level();
	$t_accessible_custom_fields_ids = array();
	$t_accessible_custom_fields_names = array();
	$t_accessible_custom_fields_values = array();
	$t_filter_cols = 8;
	$t_custom_cols = 1;
	$t_custom_rows = 0;

	if ( ON == config_get( 'filter_by_custom_fields' ) ) {
		$t_custom_cols = config_get( 'filter_custom_fields_per_row' );
		$t_custom_fields = custom_field_get_ids( $t_project_id );

		foreach ( $t_custom_fields as $t_cfid ) {
			$t_field_info = custom_field_cache_row( $t_cfid, true );
			if ( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
				$t_accessible_custom_fields_ids[] = $t_cfid;
				$t_accessible_custom_fields_names[] = $t_field_info['name'];
				$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_cfid );
			}
		}

		if ( sizeof( $t_accessible_custom_fields_ids ) > 0 ) {
			$t_per_row = config_get( 'filter_custom_fields_per_row' );
			$t_custom_rows = ceil( sizeof( $t_accessible_custom_fields_ids ) / $t_per_row );
		}
	}		

	$t_filter = current_user_get_bug_filter();
	$t_project_id = helper_get_current_project();
	$f_for_screen = gpc_get_bool( 'for_screen', true );

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];
	$t_action  = "view_all_set.php?f=3";

	if ( $f_for_screen == false ) 
	{
		$t_action  = "view_all_set.php";
	}
	
?>
<br />
<form method="post" name="filters" action="<?php echo $t_action; ?>">
<input type="hidden" name="type" value="1" />
<?php 
	if ( $f_for_screen == false ) 
	{
		print "<input type=\"hidden\" name=\"print\" value=\"1\" />";
		print "<input type=\"hidden\" name=\"offset\" value=\"0\" />";
	}	
?>
<input type="hidden" name="sort" value="<?php echo $t_sort ?>" />
<input type="hidden" name="dir" value="<?php echo $t_dir ?>" />

<table class="width100" cellspacing="0">
<tr class="row-category2">
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'reporter' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'assigned_to' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'category' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'severity' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'status' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'show' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'changed' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'hide_status' ) ?></td>
</tr>
<tr>
	<!-- Reporter -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="reporter_id">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php print_reporter_option_list( $t_filter['reporter_id'] ) ?>
		</select>
	</td>
	<!-- Handler -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="handler_id">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="none" <?php check_selected( $t_filter['handler_id'], 'none' ); ?>><?php echo lang_get( 'none' ) ?></option>
			<option value="any"></option>
			<?php print_assign_to_option_list( $t_filter['handler_id'] ) ?>
		</select>
        <input type="checkbox" name="and_not_assigned" <?php check_checked( $t_filter['and_not_assigned'], 'on' ); ?> /> <?php echo lang_get( 'or_unassigned' ) ?>
	</td>
	<!-- Category -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="show_category">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php # This shows orphaned categories as well as selectable categories ?>
			<?php print_category_complete_option_list( $t_filter['show_category'] ) ?>
		</select>
	</td>
    <!-- Severity -->
    <td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
        <select name="show_severity">
            <option value="any"><?php echo lang_get( 'any' ) ?></option>
            <option value="any"></option>
            <?php print_enum_string_option_list( 'severity', $t_filter['show_severity'] ) ?>
        </select>
    </td>
	<!-- Status -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="show_status">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'status', $t_filter['show_status'] ) ?>
		</select>
	</td>
	<!-- Number of bugs per page -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="text" name="per_page" size="3" maxlength="7" value="<?php echo $t_filter['per_page'] ?>" />
	</td>
	<!-- Highlight changed bugs -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="text" name="highlight_changed" size="3" maxlength="7" value="<?php echo $t_filter['highlight_changed'] ?>" />
	</td>
	<!-- Hide closed and resolved bugs -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="checkbox" name="hide_resolved" <?php check_checked( $t_filter['hide_resolved'], 'on' ); ?> />&nbsp;<?php echo lang_get( 'filter_resolved' ); ?>
		<input type="checkbox" name="hide_closed" <?php check_checked( $t_filter['hide_closed'], 'on' ); ?> />&nbsp;<?php echo lang_get( 'filter_closed' ); ?>
	</td>
</tr>

<tr class="row-category2">
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'product_build' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'resolution' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 2 * $t_custom_cols ); ?>"><?php echo lang_get( 'product_version' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'start_date' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><input type="checkbox" name="do_filter_by_date" <?php check_checked( $t_filter['do_filter_by_date'], 'on' ); if ( ON == config_get( 'use_javascript' ) ) { print "onclick=\"SwitchDateFields();\""; } ?> /><?php echo lang_get( 'use_date_filters' ) ?></td>
	<td class="small-caption" colspan="<?php echo ( 2 * $t_custom_cols ); ?>"><?php echo lang_get( 'end_date' ) ?></td>
</tr>
<tr>
	<!-- Build -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="show_build">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php print_build_option_list( $t_filter['show_build'] ) ?>
		</select>
	</td>
	<!-- Resolution -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<select name="show_resolution">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'resolution', $t_filter['show_resolution'] ) ?>
		</select>
	</td>
	<!-- Version -->
	<td colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
		<select name="show_version">
			<option value="any"><?php echo lang_get( 'any' ) ?></option>
			<option value="any"></option>
			<?php print_version_option_list( $t_filter['show_version'] ) ?>
		</select>
	</td>
	<!-- Start date -->
	<td class="left" colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
		<?php
		$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
		foreach( $t_chars as $t_char ) {
			if ( strcasecmp( $t_char, "M" ) == 0 ) {
				print "<select name=\"start_month\">";
				print_month_option_list( $t_filter['start_month'] );
				print "</select>\n";
			}
			if ( strcasecmp( $t_char, "D" ) == 0 ) {
				print "<select name=\"start_day\">";
				print_day_option_list( $t_filter['start_day'] );
				print "</select>\n";
			}
			if ( strcasecmp( $t_char, "Y" ) == 0 ) {
				print "<select name=\"start_year\">";
				print_year_option_list( $t_filter['start_year'] );
				print "</select>\n";
			}
		}
		?>
	</td>
	<!-- End date -->
	<td class="left" colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
		<?php
		$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
		foreach( $t_chars as $t_char ) {
			if ( strcasecmp( $t_char, "M" ) == 0 ) {
				print "<select name=\"end_month\">";
				print_month_option_list( $t_filter['end_month'] );
				print "</select>\n";
			}
			if ( strcasecmp( $t_char, "D" ) == 0 ) {
				print "<select name=\"end_day\">";
				print_day_option_list( $t_filter['end_day'] );
				print "</select>\n";
			}
			if ( strcasecmp( $t_char, "Y" ) == 0 ) {
				print "<select name=\"end_year\">";
				print_year_option_list( $t_filter['end_year'] );
				print "</select>\n";
			}
		}
		?>
	</td>
</tr>

<?php
if ( ON == config_get( 'filter_by_custom_fields' ) ) {
?>
	<?php # -- Custom Field Searching -- ?>
	<?php 
	if ( sizeof( $t_accessible_custom_fields_ids ) > 0 ) {
		$t_per_row = config_get( 'filter_custom_fields_per_row' );
		$t_num_rows = ceil( sizeof( $t_accessible_custom_fields_ids ) / $t_per_row );
		$t_base = 0;
		
		for ( $i = 0; $i < $t_num_rows; $i++ ) {
			?>
			<tr class="row-category2">
			<?php
			for( $j = 0; $j < $t_per_row; $j++ ) {
				echo '<td class="small-caption" colspan="' . ( 1 * $t_filter_cols ) . '">';
				if ( isset( $t_accessible_custom_fields_names[$t_base + $j] ) ) {
					echo $t_accessible_custom_fields_names[$t_base + $j];
				} else {
					echo '&nbsp;';
				}
				echo '</td>';
			}
			?>
			</tr>
			<tr>
			<?php
			for ( $j = 0; $j < $t_per_row; $j++ ) {
				echo '<td colspan="' . ( 1 * $t_filter_cols ) . '">';
				if ( isset( $t_accessible_custom_fields_names[$t_base + $j] ) ) {
					echo '<select name="custom_field_' . $t_accessible_custom_fields_ids[$t_base + $j] .'">';
					echo '<option value="any">' . lang_get( 'any' ) .'</option>';
					echo '<option value=""></option>';
					foreach( $t_accessible_custom_fields_values[$t_base + $j] as $t_item ) {
						if ( ( strtolower( $t_item ) != "any" ) && ( trim( $t_item ) != "" ) ) {
							echo '<option value="' .  htmlentities( $t_item )  . '" ';
							if ( isset( $t_filter['custom_fields'][ $t_accessible_custom_fields_ids[$t_base + $j] ] ) ) {
								check_selected( $t_item, $t_filter['custom_fields'][ $t_accessible_custom_fields_ids[$t_base + $j] ] );
							}
							echo '>' . $t_item  . '</option>' . "\n";
						}
					}
					echo '</select>';
				} else {
					echo '&nbsp;';
				}
				echo '</td>';
			}
			
			?>
			</tr>
			<?php
			$t_base += $t_per_row;
		}
	}
}
?>

<tr class="row-category2">
<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'search' ) ?></td>
<td class="small-caption" colspan="<?php echo ( 7 * $t_custom_cols ); ?>"></td>
</tr>
<tr>
	<!-- Search field -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="text" size="16" name="search" value="<?php echo $t_filter['search']; ?>" />
	</td>

	<td class="small-caption" colspan="<?php echo ( 6 * $t_custom_cols ); ?>"></td>
			
	<!-- Submit button -->
	<td class="right" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="submit" name="filter" value="<?php echo lang_get( 'filter_button' ) ?>" />
	</td>
</tr>
<tr>
	<td colspan="<?php echo ( 12 * $t_custom_cols ); ?>">
	</td>
</tr>
</table>
</form>

<?php html_page_bottom1( __FILE__ ) ?>