<?php
	# Send headers to browser to active mime loading
	# Leigh Morresi <leighm@linuxbandwagon.com>

	#header( "Content-Type: plain/text; name=$email_project-$_page_title.csv;" );
	#header( "Content-Transfer-Encoding: BASE64;" );
	#header( "Content-Disposition: attachment; filename=$email_project-$_page_title.csv;" );

	echo "$s_email_project,$g_page_title \n\n";
	echo "$s_priority,$s_id,$s_severity,$s_status,$s_updated,$s_summary\n";

	for ( $i=0; $i < $row_count; $i++ ) {
		# prefix bug data with v_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$t_last_updated = date( $g_short_date_format, $v_last_updated );
		$t_priority 	= get_enum_element( "priority", $v_priority );
		$t_status 		= get_enum_element( "status", $v_status );
		$t_severity		= get_enum_element( "severity", $v_severity );
		echo "$t_priority,$v_id,$t_severity,$t_status,$t_last_updated,\"$v_summary\"\n";
    }
?>