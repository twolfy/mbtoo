<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bugnote_add_inc.php,v 1.16 2004-01-11 07:16:06 vboctor Exp $
	# --------------------------------------------------------
?>
<?php if ( ( $t_bug->status < config_get( 'bug_resolved_status_threshold' ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>
<br />
<a name="addbugnote">
<form method="post" action="bugnote_add.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<?php echo lang_get( 'add_bugnote_title' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'bugnote' ) ?>
	</td>
	<td width="75%">
		<textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<?php if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'private' ) ?>
	</td>
	<td>
		<input type="checkbox" name="private" />
	</td>
</tr>
<?php } ?>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'add_bugnote_button' ) ?>" />
	</td>
</tr>
</table>
</form>
<?php # Bugnote Add Form END ?>
<?php } ?>
