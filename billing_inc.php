<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: billing_inc.php,v 1.9 2007-04-22 07:45:33 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the bug bugnote_stats

	# $f_bug_id must already be defined
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	if ( ! config_get('time_tracking_enabled') )
		return;
?>

<a name="bugnotestats" id="bugnotestats" /><br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="bugnotestats_closed" style="display: none;">
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<a href="" onClick="ToggleDiv( 'bugnotestats', g_div_bugnotestats ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="bugnotestats_open">
<?php
	$t_today = date( "d:m:Y" );
	$t_date_submitted = isset( $t_bug ) ? date( "d:m:Y", $t_bug->date_submitted ) : $t_today;

	$t_bugnote_stats_from_def = $t_date_submitted;
	$t_bugnote_stats_from_def_ar = explode ( ":", $t_bugnote_stats_from_def );
	$t_bugnote_stats_from_def_d = $t_bugnote_stats_from_def_ar[0];
	$t_bugnote_stats_from_def_m = $t_bugnote_stats_from_def_ar[1];
	$t_bugnote_stats_from_def_y = $t_bugnote_stats_from_def_ar[2];

	$t_bugnote_stats_from_d = gpc_get_string('start_day', $t_bugnote_stats_from_def_d);
	$t_bugnote_stats_from_m = gpc_get_string('start_month', $t_bugnote_stats_from_def_m);
	$t_bugnote_stats_from_y = gpc_get_string('start_year', $t_bugnote_stats_from_def_y);

	$t_bugnote_stats_to_def = $t_today;
	$t_bugnote_stats_to_def_ar = explode ( ":", $t_bugnote_stats_to_def );
	$t_bugnote_stats_to_def_d = $t_bugnote_stats_to_def_ar[0];
	$t_bugnote_stats_to_def_m = $t_bugnote_stats_to_def_ar[1];
	$t_bugnote_stats_to_def_y = $t_bugnote_stats_to_def_ar[2];

	$t_bugnote_stats_to_d = gpc_get_string('end_day', $t_bugnote_stats_to_def_d);
	$t_bugnote_stats_to_m = gpc_get_string('end_month', $t_bugnote_stats_to_def_m);
	$t_bugnote_stats_to_y = gpc_get_string('end_year', $t_bugnote_stats_to_def_y);

	$f_get_bugnote_stats_button = gpc_get_string('get_bugnote_stats_button', '');
	$f_bugnote_cost = gpc_get_string( 'bugnote_cost', '' );
	$f_project_id = helper_get_current_project();

	if ( ON == config_get( 'time_tracking_with_billing' ) ) {
		$t_cost_col = true;
	} else {
		$t_cost_col = false;
	}

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="id" value="<?php echo isset( $f_bug_id ) ? $f_bug_id : 0 ?>" />
<table border=0 class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onClick="ToggleDiv( 'bugnotestats', g_div_bugnotestats ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
</tr>
<tr class="row-2">
        <td class="category" width="25%">
                <?php
		$t_filter = array();
		$t_filter['do_filter_by_date'] = 'on';
		$t_filter['start_day'] = $t_bugnote_stats_from_d;
		$t_filter['start_month'] = $t_bugnote_stats_from_m;
		$t_filter['start_year'] = $t_bugnote_stats_from_y;
		$t_filter['end_day'] = $t_bugnote_stats_to_d;
		$t_filter['end_month'] = $t_bugnote_stats_to_m;
		$t_filter['end_year'] = $t_bugnote_stats_to_y;
		print_filter_do_filter_by_date(true);
		?>
        </td>
</tr>
<?php if ( $t_cost_col ) { ?>
<tr class="row-1">
	<td>
		<?php echo lang_get( 'time_tracking_cost' ) ?>:
		<input type="text" name="bugnote_cost" value="<?php echo $f_bugnote_cost ?>" />
	</td>
</tr>
<?php } ?>
<tr>
        <td class="center" colspan="2">
                <input type="submit" class="button" name="get_bugnote_stats_button" value="<?php echo lang_get( 'time_tracking_get_info_button' ) ?>" />
        </td>
</tr>

</table>
</form>
<?php
if ( !is_blank( $f_get_bugnote_stats_button ) ) {
	$t_from = "$t_bugnote_stats_from_y-$t_bugnote_stats_from_m-$t_bugnote_stats_from_d";
	$t_to = "$t_bugnote_stats_to_y-$t_bugnote_stats_to_m-$t_bugnote_stats_to_d";
	$t_bugnote_stats = bugnote_stats_get_project_array( $f_project_id, $t_from, $t_to, $f_bugnote_cost );

	if ( is_blank( $f_bugnote_cost ) || ( (double)$f_bugnote_cost == 0 ) ) {
		$t_cost_col = false;
    }

	$t_prev_id = -1;
?>
<table border=0 class="width100" cellspacing="0">
<tr class="row-category-history">
	<td class="small-caption">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td class="small-caption">
		<?php echo lang_get( 'time_tracking' ) ?>
	</td>
<?php if ( $t_cost_col) { ?>
	<td class="small-caption">
		<?php echo lang_get( 'time_tracking_cost' ) ?>
	</td>
<?php } ?>

</tr>
<?php foreach ( $t_bugnote_stats as $t_item ) { ?>
<?php
	$t_item['sum_time_tracking'] = db_minutes_to_hhmm( $t_item['sum_time_tracking'] );
	if ( $t_item['bug_id'] != $t_prev_id) {
		$t_link = string_get_bug_view_link( $t_item['bug_id'] ) . ": " . string_display( $t_item['summary'] );
		echo "<tr class='row-category-history'><td colspan=4>".$t_link."</td></tr>";
		$t_prev_id = $t_item['bug_id'];
	}
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="small-caption">
		<?php echo $t_item['username'] ?>
	</td>
	<td class="small-caption">
		<?php echo $t_item['sum_time_tracking'] ?>
	</td>
<?php if ($t_cost_col) { ?>
	<td>
		<?php echo string_attribute( number_format( $t_item['cost'], 2 ) ); ?>
	</td>
<?php } ?>
</tr>
<?php } # end for loop ?>
</table>
<?php } # end if ?>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
	SetDiv( "bugnotestats", g_div_bugnotestats );
</script>
<?php } ?>
