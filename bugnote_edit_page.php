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
	 * CALLERS
	 *	This page is submitted to by the following pages:
	 *	- bugnote_inc.php
	 *
	 * EXPECTED BEHAVIOUR
	 *	Allow the user to modify the text of a bugnote, then submit to
	 *	bugnote_update.php with the new text
	 *
	 * RESTRICTIONS & PERMISSIONS
	 *	- none beyond API restrictions
	 *
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
	require_once( $t_core_path.'bugnote_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

	$t_bug = bug_get( $t_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) ||
	 	( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}

	# Check if the bug is readonly
	if ( bug_is_readonly( $t_bug_id ) ) {
		error_parameters( $t_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$t_bugnote_text = string_textarea( bugnote_get_text( $f_bugnote_id ) );

	# No need to gather the extra information if not used
	if ( config_get('time_tracking_enabled') &&
		access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
		$t_time_tracking = bugnote_get_field( $f_bugnote_id, "time_tracking" );
		$t_time_tracking = db_minutes_to_hhmm( $t_time_tracking );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $t_bug_id );

	html_page_top( bug_format_summary( $t_bug_id, SUMMARY_CAPTION ) );
?>
<br />
<div align="center">
<form method="post" action="bugnote_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>" />
		<?php echo lang_get( 'edit_bugnote_title' ) ?>
	</td>
	<td class="right">
		<?php print_bracket_link( $t_redirect_url, lang_get( 'go_back' ) ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea cols="80" rows="10" name="bugnote_text"><?php echo $t_bugnote_text ?></textarea>
	</td>
</tr>
<?php if ( config_get('time_tracking_enabled') ) { ?>
<?php if ( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) { ?>
<tr class="row-2">
	<td class="center" colspan="2">
		<b><?php echo lang_get( 'time_tracking') ?> (HH:MM)</b><br />
		<input type="text" name="time_tracking" size="5" value="<?php echo $t_time_tracking ?>" />
	</td>
</tr>
<?php } ?>
<?php } ?>

<?php event_signal( 'EVENT_BUGNOTE_EDIT_FORM', array( $t_bug_id, $f_bugnote_id ) ); ?>

<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom( __FILE__ );
