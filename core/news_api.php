<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_api.php,v 1.16 2004-04-21 14:15:32 vboctor Exp $
	# --------------------------------------------------------

	### News API ###

	# --------------------
	# Add a news item
	function news_create( $p_project_id, $p_poster_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_poster_id	= db_prepare_int( $p_poster_id );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_announcement	= db_prepare_bool( $p_announcement );
		$c_headline		= db_prepare_string( $p_headline );
		$c_body			= db_prepare_string( $p_body );

		if ( is_blank( $c_headline ) || is_blank( $c_body ) ) {
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_news_table = config_get( 'mantis_news_table' );

		# Add item
		$query = "INSERT
				INTO $t_news_table
	    		  ( project_id, poster_id, date_posted, last_modified,
	    		    view_state, announcement, headline, body )
				VALUES
				  ( '$c_project_id', '$c_poster_id', " . db_now() . "," . db_now() .",
				    '$c_view_state', '$c_announcement', '$c_headline', '$c_body' )";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Delete the news entry
	function news_delete( $p_news_id ) {
		$c_news_id = db_prepare_int( $p_news_id );

		$t_news_table = config_get( 'mantis_news_table' );

		$query = "DELETE FROM $t_news_table
	    		  WHERE id='$c_news_id'";

		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Delete the news entry
	function news_delete_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_news_table = config_get( 'mantis_news_table' );

		$query = "DELETE FROM $t_news_table
	    		  WHERE project_id='$c_project_id'";

		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Update news item
	function news_update( $p_news_id, $p_project_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
		$c_news_id		= db_prepare_int( $p_news_id );
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_view_state	= db_prepare_int( $p_view_state );
		$c_announcement	= db_prepare_bool( $p_announcement );
		$c_headline		= db_prepare_string( $p_headline );
		$c_body			= db_prepare_string( $p_body );

		if ( is_blank( $c_headline ) || is_blank( $c_body ) ) {
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_news_table = config_get( 'mantis_news_table' );

		# Update entry
		$query = "UPDATE $t_news_table
				  SET view_state='$c_view_state',
					announcement='$c_announcement',
					headline='$c_headline',
					body='$c_body',
					project_id='$c_project_id',
					last_modified= " . db_now() . "
				  WHERE id='$c_news_id'";

		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
	# --------------------
	# Selects the news item associated with the specified id
	function news_get_row( $p_news_id ) {
		$c_news_id = db_prepare_int( $p_news_id );

		$t_news_table = config_get( 'mantis_news_table' );

		$query = "SELECT *, date_posted
				  FROM $t_news_table
				  WHERE id='$c_news_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			trigger_error( ERROR_NEWS_NOT_FOUND, ERROR );
		} else {
			$row = db_fetch_array( $result );
			$row['date_posted'] = db_unixtimestamp ( $row['date_posted'] );
			return $row;
		}
	}
	# --------------------
	# get news count (selected project plus sitewide posts)
	function news_get_count( $p_project_id, $p_sitewide=true ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_news_table = config_get( 'mantis_news_table' );

		$query = "SELECT COUNT(*)
				  FROM $t_news_table
				  WHERE project_id='$c_project_id'";

		if ( $p_sitewide ) {
			$query .= ' OR project_id=' . ALL_PROJECTS;
		}

		$result = db_query( $query );

	    return db_result( $result, 0, 0 );
	}
	# --------------------
	# get news items (selected project plus sitewide posts)
	function news_get_rows( $p_project_id, $p_sitewide=true ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_news_table = config_get( 'mantis_news_table' );

		$query = "SELECT *, date_posted
				  FROM $t_news_table
				  WHERE project_id='$c_project_id'";

		if ( $p_sitewide ) {
			$query .= ' OR project_id=' . ALL_PROJECTS;
		}

		$query .= " ORDER BY date_posted DESC";

		$result = db_query( $query );

		$t_rows = array();
		$t_row_count = db_num_rows( $result );

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			$row = db_fetch_array( $result ) ;
			$row['date_posted'] = db_unixtimestamp( $row['date_posted'] );
			array_push( $t_rows, $row );
		}

		return $t_rows;
	}
	# --------------------
	# Check if the specified news item is private
	function news_get_field( $p_news_id, $p_field_name ) {
		$row = news_get_row( $p_news_id );
		return ( $row[$p_field_name] );
	}
	# --------------------
	# Check if the specified news item is private
	function news_is_private( $p_news_id ) {
		return ( news_get_field( $p_news_id, 'view_state' ) == VS_PRIVATE );
	}
	# --------------------
	# Gets a limited set of news rows to be viewed on one page based on the criteria
	# defined in the configuration file.
	function news_get_limited_rows( $p_offset, $p_project_id = null ) {
		if ( $p_project_id === null ) {
			$p_project_id = helper_get_current_project();
		}

		$c_project_id	= db_prepare_int( $p_project_id );
		$c_offset		= db_prepare_int( $p_offset );

		$t_news_table			= config_get( 'mantis_news_table' );
		$t_news_view_limit		= config_get( 'news_view_limit' );
		$t_news_view_limit_days = config_get( 'news_view_limit_days' );

		switch ( config_get( 'news_limit_method' ) ) {
			case 0 :
				# Select the news posts
				$query = "SELECT *, date_posted
						FROM $t_news_table
						WHERE project_id='$c_project_id' OR project_id=" . ALL_PROJECTS . "
						ORDER BY announcement DESC, id DESC";
				$result = db_query( $query , $t_news_view_limit , $c_offset);
				break;
			case 1 :
				# Select the news posts @@BUGBUG-query
				$query = "SELECT *, date_posted
						FROM $t_news_table
						WHERE ( project_id='$c_project_id' OR project_id=" . ALL_PROJECTS . " ) AND
							" . db_helper_compare_days( db_now(), 'date_posted', "< $t_news_view_limit_days") . "
						ORDER BY announcement DESC, id DESC";
				$result = db_query( $query );
				break;
		} # end switch

		$t_row_count = db_num_rows( $result );

		$t_rows = array();
		for ( $i = 0; $i < $t_row_count; $i++ ) {
			$row = db_fetch_array( $result ) ;
			$row['date_posted'] = db_unixtimestamp( $row['date_posted'] );
			array_push( $t_rows, $row );
		}

		return $t_rows;
	}
?>