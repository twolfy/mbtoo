<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	# grab user data and prefix with u_
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE id='$f_id'";
    $result = db_query($query);
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu() ?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<form method="post" action="<?php echo $g_manage_user_update ?>">
<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
<tr>
	<td class="form-title" colspan="3">
		<?php echo $s_edit_user_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_username ?>:
	</td>
	<td colspan="2">
		<input type="text" size="16" maxlength="32" name="f_username" value="<?php echo $u_username ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>:
	</td>
	<td colspan="2">
		<input type="text" size="32" maxlength="64" name="f_email" value="<?php echo $u_email ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_access_level ?>:
	</td>
	<td colspan="2">
		<select name="f_access_level">
			<?php print_enum_string_option_list( $s_access_levels_enum_string, $u_access_level ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_enabled ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_enabled" <?php if ( ON == $u_enabled ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_protected ?>
	</td>
	<td colspan="2">
		<input type="checkbox" name="f_protected" <?php if ( ON == $u_protected ) echo "CHECKED" ?>>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_update_user_button ?>">
	</td>
</form>
	<form method="post" action="<?php echo $g_manage_user_reset ?>">
	<td class="center">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
		<input type="hidden" name="f_email" value="<?php echo $u_email ?>">
		<input type="hidden" name="f_protected" value="<?php echo $u_protected ?>">
		<input type="submit" value="<?php echo $s_reset_password_button ?>">
	</td>
	</form>
	<form method="post" action="<?php echo $g_manage_user_delete_page ?>">
	<td class="center">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
		<input type="hidden" name="f_protected" value="<?php echo $u_protected ?>">
		<input type="submit" value="<?php echo $s_delete_user_button ?>">
	</td>
	</form>
</tr>
</table>
</div>

<p>
<div align="center">
	<?php echo $s_reset_password_msg ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>