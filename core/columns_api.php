<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	/**
	 * Get all accessible columns for the current project / current user..
	 */
	function columns_get_all() {
		$t_columns = array(
			'additional_information',   // new
			'attachment',
			'bugnotes_count',
			'build',
			'category',
			'date_submitted',
			'description',     // new
			'duplicate_id',
			'edit',
			'eta',
			'fixed_in_version',
			'handler_id',
			'id',
			'last_updated',
			'os',
			'os_build',
			'platform',
			'priority',
			'project_id',
			'projection',
			'reporter_id',
			'reproducibility',
			'resolution',
			'selection',
			'severity',
			'sponsorship_total',
			'status',
			'steps_to_reproduce',        // new
			'summary',
			'version',
			'view_state',
		);

		# Add project custom fields to the array.  Only add the ones for which the current user has at least read access.
		$t_project_id = helper_get_current_project();
		$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );
		foreach( $t_related_custom_field_ids as $t_id ) {
			if ( !custom_field_has_read_access_by_project_id( $t_id, $t_project_id ) ) {
				continue;
			}

			$t_def = custom_field_get_definition( $t_id );
			$t_columns[] = 'custom_' . $t_def['name'];
		} # foreach

		return $t_columns;
	}

	/**
	 * Checks if the specified column is an extended column.  Extended columns are native columns that are
	 * associated with the issue but are saved in mantis_bug_text_table.
	 * @param $p_column The column name
	 * @returns true for extended; false otherwise.
	 */
	function column_is_extended( $p_column ) {
		switch ( $p_column ) {
			case 'description':
			case 'steps_to_reproduce':
			case 'additional_information':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Given a column name from the array of columns to be included in a view, this method checks if
	 * the column is a custom column and if so returns its name.  Note that for custom fields, then
	 * provided names will have the "custom_" prefix, where the returned ones won't have the prefix.
	 *
	 * @param $p_column Column name.
	 * @returns The custom field column name or null if the specific column is not a custom field 
	 * or invalid column.
	 */
	function column_get_custom_field_name( $p_column ) {
		if ( strpos( $p_column, 'custom_' ) === 0 ) {
			return substr( $p_column, 7 );
		}

		return null;
	}

	/**
	 * Converts a string of comma separate column names to an array.
	 * @param $p_string - Comma separate column name (not case sensitive)
	 * @returns The array with all column names lower case.
	 */
	function columns_string_to_array( $p_string ) {
		$t_string = str_replace( ' ', '', $p_string );
		$t_string = strtolower( $t_string );

		$t_columns = explode( ',', $t_string );

		return $t_columns;
	}

	/**
	 * Gets the localized title for the specified column.  The column can be native or custom.
	 The custom fields must contain the 'custom_' prefix.
	 * @ $p_column - The column name.
	 * @returns The column localized name.
	 */
	function column_get_title( $p_column ) {
		$t_custom_field = column_get_custom_field_name( $p_column );
		if ( $t_custom_field !== null ) {
			$t_field_id = custom_field_get_id_from_name( $t_custom_field );

			if ( $t_field_id === false ) {
				$t_custom_field = '@' . $t_custom_field . '@';
			} else {
				$t_def = custom_field_get_definition( $t_field_id );
				$t_custom_field = lang_get_defaulted( $t_def['name'] );
			}

			return $t_custom_field;
		}

		switch( $p_column ) {
			case 'attachment':
				return lang_get( 'attachments' );
			case 'bugnotes_count':
				return '#';
			case 'category_id':
				return lang_get( 'category' );
			case 'edit':
				return '';
			case 'handler_id':
				return lang_get( 'assigned_to' );
			case 'last_updated':
				return lang_get( 'updated' );
			case 'os_build':
				return lang_get( 'os_version' );
			case 'project_id':
				return lang_get( 'email_project' );
			case 'reporter_id':
				return lang_get( 'reporter' );
			case 'selection':
				return '';
			case 'sponsorship_total':
				return sponsorship_get_currency();
			case 'version':
				return lang_get( 'product_version' );
			case 'view_state':
				return lang_get( 'view_status' );
			default:
				return lang_get( $p_column );
		}
	}

	/**
	 * Checks an array of columns for duplicate or invalid fields.
	 * @param $p_field_name - The logic name of the array being validated.  Used when triggering errors.
	 * @param $p_columns_to_validate - The array of columns to validate.
	 * @param $p_columns_all - The list of all valid columns.
	 */
	function columns_ensure_valid( $p_field_name, $p_columns_to_validate, $p_columns_all ) {
		$t_columns_no_duplicates = array();

		# Check for invalid fields
		foreach ( $p_columns_to_validate as $t_column ) {
			if ( !in_array( strtolower( $t_column ), $p_columns_all ) ) {
				error_parameters( $p_field_name, $t_column );
				trigger_error( ERROR_COLUMNS_INVALID, ERROR );
				return false;
			}
		}

		# Check for duplicate fields
		foreach ( $p_columns_to_validate as $t_column ) {
			$t_column_lower = strtolower( $t_column );
			if ( in_array( $t_column, $t_columns_no_duplicates ) ) {
				error_parameters( $p_field_name, $t_column );
				trigger_error( ERROR_COLUMNS_DUPLICATE, ERROR );
			} else {
				$t_columns_no_duplicates[] = $t_column_lower;
			}
		}

		return true;
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_selection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td> &nbsp; </td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_edit( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td> &nbsp; </td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'id' ), 'id', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'id' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_project_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'email_project' ), 'project_id', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'project_id' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_duplicate_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'duplicate_id' ), 'duplicate_id', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'duplicate_id' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_reporter_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'reporter' ), 'reporter_id', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'reporter_id' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_handler_id( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'assigned_to' ), 'handler_id', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'handler_id' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_priority( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'priority_abbreviation' ), 'priority', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'priority' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_reproducibility( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'reproducibility' ), 'reproducibility', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'reproducibility' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_projection( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'projection' ), 'projection', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'projection' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_eta( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'eta' ), 'eta', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'eta' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_resolution( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'resolution' ), 'resolution', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'resolution' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_fixed_in_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'fixed_in_version' ), 'fixed_in_version', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'fixed_in_version' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_target_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'target_version' ), 'target_version', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'target_version' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_view_state( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'view_status' ), 'view_state', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'view_state' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_os( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'os' ), 'os', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'os' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_os_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'os_version' ), 'os_build', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'os_build' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_build( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		if ( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
			echo '<td>';
			print_view_bug_sort_link( lang_get( 'build' ), 'build', $p_sort, $p_dir, $p_columns_target );
			print_sort_icon( $p_dir, $p_sort, 'build' );
			echo '</td>';
		} else {
			echo lang_get( 'build' );
		}
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_platform( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'platform' ), 'platform', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'platform' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_version( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'product_version' ), 'version', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'version' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_date_submitted( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'date_submitted' ), 'date_submitted', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'date_submitted' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_attachment( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;

		$t_show_attachments = config_get( 'show_attachment_indicator' );

		if ( ON == $t_show_attachments ) {
			echo "\t<td>";
			echo '<img src="' . $t_icon_path . 'attachment.png' . '" alt="" />';
			echo "</td>\n";
		}
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_category( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'category' ), 'category', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'category' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_sponsorship_total( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo "\t<td>";
		print_view_bug_sort_link( sponsorship_get_currency(), 'sponsorship_total', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'sponsorship_total' );
		echo "</td>\n";
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_severity( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'severity' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_status( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'status' ), 'status', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'status' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_last_updated( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'last_updated' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_summary( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $p_sort, $p_dir, $p_columns_target );
		print_sort_icon( $p_dir, $p_sort, 'summary' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_bugnotes_count( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td> # </td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_description( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		echo lang_get( 'description' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_steps_to_reproduce( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		echo lang_get( 'steps_to_reproduce' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_title_additional_information( $p_sort, $p_dir, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		echo lang_get( 'additional_information' );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_selection( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_checkboxes_exist, $t_update_bug_threshold;

		echo '<td>';
		if ( access_has_bug_level( $t_update_bug_threshold, $p_row['id'] ) ) {
			$t_checkboxes_exist = true;
			printf( "<input type=\"checkbox\" name=\"bug_arr[]\" value=\"%d\" />" , $p_row['id'] );
		} else {
			echo "&nbsp;";
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_edit( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path, $t_update_bug_threshold;

		echo '<td>';
		if ( !bug_is_readonly( $p_row['id'] )
			&& access_has_bug_level( $t_update_bug_threshold, $p_row['id'] ) ) {
			echo '<a href="' . string_get_bug_update_url( $p_row['id'] ) . '">';
			echo '<img border="0" width="16" height="16" src="' . $t_icon_path . 'update.png';
			echo '" alt="' . lang_get( 'update_bug_button' ) . '"';
			echo ' title="' . lang_get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_priority( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		if ( ON == config_get( 'show_priority_text' ) ) {
			print_formatted_priority_string( $p_row['status'], $p_row['priority'] );
		} else {
			print_status_icon( $p_row['priority'] );
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_id( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';
		print_bug_link( $p_row['id'], false );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_sponsorship_total( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo "\t<td class=\"right\">";

		if ( $p_row['sponsorship_total'] > 0 ) {
			$t_sponsorship_amount = sponsorship_format_amount( $p_row['sponsorship_total'] );
			echo string_no_break( $t_sponsorship_amount );
		}

		echo "</td>\n";
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_bugnotes_count( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_filter;

		# grab the bugnote count
		$t_bugnote_stats = bug_get_bugnote_stats( $p_row['id'] );
		if ( NULL !== $t_bugnote_stats ) {
			$bugnote_count = $t_bugnote_stats['count'];
			$v_bugnote_updated = $t_bugnote_stats['last_modified'];
		} else {
			$bugnote_count = 0;
		}

		echo '<td class="center">';
		if ( $bugnote_count > 0 ) {
			$t_bugnote_link = '<a href="' . string_get_bug_view_url( $p_row['id'] )
				. '&amp;nbn=' . $bugnote_count . '#bugnotes">'
				. $bugnote_count . '</a>';

			if ( $v_bugnote_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
				printf( '<span class="bold">%s</span>', $t_bugnote_link );
			} else {
				echo $t_bugnote_link;
			}
		} else {
			echo '&nbsp;';
		}

		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_attachment( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;

		# Check for attachments
		$t_attachment_count = 0;
		if ( file_can_view_bug_attachments( $p_row['id'] ) ) {
			$t_attachment_count = file_bug_attachment_count( $p_row['id'] );
		}

		echo "\t<td>";

		if ( 0 < $t_attachment_count ) {
			echo '<a href="' . string_get_bug_view_url( $p_row['id'] ) . '#attachments">';
			echo '<img border="0" src="' . $t_icon_path . 'attachment.png' . '"';
			echo ' alt="' . lang_get( 'attachment_alt' ) . '"';
			echo ' title="' . $t_attachment_count . ' ' . lang_get( 'attachments' ) . '"';
			echo ' />';
			echo '</a>';
		} else {
			echo ' &nbsp; ';
		}

		echo "</td>\n";
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_category_id( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_sort, $t_dir;

		# grab the project name
		$t_project_name = project_get_field( $p_row['project_id'], 'name' );

		echo '<td class="center">';

		# type project name if viewing 'all projects' or if issue is in a subproject
		if ( ON == config_get( 'show_bug_project_links' )
		  && helper_get_current_project() != $p_row['project_id'] ) {
			echo '<small>[';
			print_view_bug_sort_link( $t_project_name, 'project_id', $t_sort, $t_dir, $p_columns_target );
			echo ']</small><br />';
		}

		echo string_display( category_full_name( $p_row['category_id'], false ) );

		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_severity( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">';
		print_formatted_severity_string( $p_row['status'], $p_row['severity'] );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_eta( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">', get_enum_element( 'eta', $p_row['eta'] ), '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_resolution( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">', get_enum_element( 'resolution', $p_row['resolution'] ), '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_status( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">';
		printf( '<span class="issue-status" title="%s">%s</span>'
			, get_enum_element( 'resolution', $p_row['resolution'] )
			, get_enum_element( 'status', $p_row['status'] )
		);

		# print username instead of status
		if ( ( ON == config_get( 'show_assigned_names' ) )
		  && ( $p_row['handler_id'] > 0 ) 
		  && ( access_has_project_level( config_get( 'view_handler_threshold' ), $p_row['project_id'] ) ) ) {
			printf( ' (%s)', prepare_user_name( $p_row['handler_id'] ) );
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_handler_id( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">';

		# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
		# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
		if ( $p_row['handler_id'] > 0 &&
			 ( helper_get_current_project() != ALL_PROJECTS ||
			   access_has_project_level( config_get( 'view_handler_threshold' ), $p_row['project_id'] ) ) ) {
			echo prepare_user_name( $p_row['handler_id'] );
		}

		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_reporter_id( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td class="center">';
		echo prepare_user_name( $p_row['reporter_id'] );
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_last_updated( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_filter;

		$t_last_updated = date( config_get( 'short_date_format' ), $p_row['last_updated'] );

		echo '<td class="center">';
		if ( $p_row['last_updated'] > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
			printf( '<span class="bold">%s</span>', $t_last_updated );
		} else {
			echo $t_last_updated;
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_date_submitted( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_date_submitted = date( config_get( 'short_date_format' ), $p_row['date_submitted'] );

		echo '<td class="center">', $t_date_submitted, '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_summary( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		global $t_icon_path;

		if ( $p_columns_target == COLUMNS_TARGET_CSV_PAGE ) {
			$t_summary = string_attribute( $p_row['summary'] );
		} else {
			$t_summary = string_display_line_links( $p_row['summary'] );
		}

		echo '<td class="left">', $t_summary;
		if ( VS_PRIVATE == $p_row['view_state'] ) {
			printf( ' <img src="%s" alt="(%s)" title="%s" />'
				, $t_icon_path . 'protected.gif'
				, lang_get( 'private' )
				, lang_get( 'private' )
			);
		}
		echo '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_description( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_bug = bug_get( $p_row['id'], true );

		$t_description = string_display_links( $t_bug->description );

		echo '<td class="left">', $t_description, '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_steps_to_reproduce( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_bug = bug_get( $p_row['id'], true );

		$t_steps_to_reproduce = string_display_links( $t_bug->additional_information );

		echo '<td class="left">', $t_steps_to_reproduce, '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_additional_information( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_bug = bug_get( $p_row['id'], true );

		$t_additional_information = string_display_links( $t_bug->additional_information );

		echo '<td class="left">', $t_additional_information, '</td>';
	}

	# --------------------
	# $p_columns_target: see COLUMNS_TARGET_* in constant_inc.php
	function print_column_target_version( $p_row, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		echo '<td>';

		# In case of a specific project, if the current user has no access to the field, then it would have been excluded from the
		# list of columns to view.  In case of ALL_PROJECTS, then we need to check the access per row.
		if ( helper_get_current_project() != ALL_PROJECTS ||
			access_has_project_level( config_get( 'roadmap_view_threshold' ), $p_row['project_id'] ) ) {
			echo $p_row['target_version'];
		}

		echo '</td>';
	}
?>
