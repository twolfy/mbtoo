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
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	if ( ! file_allow_project_upload() ) {
		access_denied();
	}

	$f_title		= gpc_get_string( 'title' );
	if ( is_blank( $f_title ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_description	= gpc_get_string( 'description' );

	$result = 0;
	$good_upload = 0;
	$disallowed = 0;
	extract( $HTTP_POST_FILES['file'], EXTR_PREFIX_ALL, 'f' );

	if ( !file_type_check( $f_name ) ) {
		$disallowed = 1;
	} else if ( is_uploaded_file( $f_tmp_name ) ) {
		$good_upload = 1;

		# grab the file path
		$t_file_path = project_get_field( helper_get_current_project(), 'file_path' );

		# prepare variables for insertion
		$f_title 	= db_prepare_string( $f_title );
		$f_description 	= db_prepare_string( $f_description );

		$f_file_name = lang_get( 'document_files_prefix' ) . '-' . project_format_id ( $g_project_cookie_val ) . '-' . $f_name;
		$t_file_size = $f_size;

		switch ( $g_file_upload_method ) {
			case DISK:	if ( !file_exists( $t_file_path.$f_file_name ) ) {
							umask( 0333 );  # make read only
							copy($f_tmp_name, $t_file_path.$f_file_name);
							$query = "INSERT INTO mantis_project_file_table
									(id, project_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
									VALUES
									(null, $g_project_cookie_val, '$f_title', '$f_description', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_type', NOW(), '')";
						} else {
							trigger_error( ERROR_DUPLICATE_FILE, ERROR );
						}
						break;
			case DATABASE:
						$t_content = addslashes( fread ( fopen( $f_tmp_name, 'rb' ), $t_file_size ) );
						$query = "INSERT INTO mantis_project_file_table
								(id, project_id, title, description, diskfile, filename, folder, filesize, file_type, date_added, content)
								VALUES
								(null, $g_project_cookie_val, '$f_title', '$f_description', '$t_file_path$f_file_name', '$f_file_name', '$t_file_path', $t_file_size, '$f_type', NOW(), '$t_content')";
						break;
		}
		$result = db_query( $query );
	}

	$t_redirect_url = 'proj_doc_page.php';
?>
<?php html_page_top1() ?>
<?php
	if ( $result ) {
		html_meta_redirect( $t_redirect_url, $g_wait_time );
	}
?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		print lang_get( 'operation_successful' ) . '<br />';
	} else {						# FAILURE
		if ( 1 == $disallowed ) {
			print error_string( ERROR_FILE_DISALLOWED ).'<br />';
		} else if ( 0 == $good_upload ) {
			print error_string( ERROR_NO_FILE_SPECIFIED ).'<br />';
		} else if ( !$result ) {
			print_sql_error( $query );
		}
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
