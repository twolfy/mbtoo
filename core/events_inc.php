<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.

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

#
# Event Declarations
# Please view the Plugin Events Reference for details on each event.
# http://www.mantisbt.org/wiki/doku.php/mantisbt:plugins_events
#

# Declare supported plugin events
event_declare_many( array(

	##### Events specific to plugins
	'EVENT_PLUGIN_INIT' 				=> EVENT_TYPE_EXECUTE,

	##### Mantis Layout Events
	'EVENT_LAYOUT_RESOURCES'			=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_BODY_BEGIN'			=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_PAGE_HEADER'			=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_CONTENT_BEGIN'		=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_CONTENT_END'			=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_PAGE_FOOTER'			=> EVENT_TYPE_OUTPUT,
	'EVENT_LAYOUT_BODY_END'				=> EVENT_TYPE_OUTPUT,

	##### Events for displaying data
	'EVENT_DISPLAY_TEXT'				=> EVENT_TYPE_CHAIN,
	'EVENT_DISPLAY_FORMATTED'			=> EVENT_TYPE_CHAIN,
	'EVENT_DISPLAY_RSS'					=> EVENT_TYPE_CHAIN,
	'EVENT_DISPLAY_EMAIL'				=> EVENT_TYPE_CHAIN,

	##### Menu Events
	'EVENT_MENU_MAIN'					=> EVENT_TYPE_DEFAULT,
	'EVENT_MENU_MANAGE'					=> EVENT_TYPE_DEFAULT,
	'EVENT_MENU_MANAGE_CONFIG'			=> EVENT_TYPE_DEFAULT,
	'EVENT_MENU_SUMMARY'				=> EVENT_TYPE_DEFAULT,
	'EVENT_MENU_DOCS'					=> EVENT_TYPE_DEFAULT,
	'EVENT_MENU_ACCOUNT'				=> EVENT_TYPE_DEFAULT,
	
	##### Bug view events
	'EVENT_VIEW_BUG_DETAILS'			=> EVENT_TYPE_EXECUTE,
	'EVENT_VIEW_BUGNOTES_START'			=> EVENT_TYPE_EXECUTE,
	'EVENT_VIEW_BUGNOTE'				=> EVENT_TYPE_EXECUTE,
	'EVENT_VIEW_BUGNOTES_END'			=> EVENT_TYPE_EXECUTE,
	'EVENT_VIEW_BUGNOTE_ADD'			=> EVENT_TYPE_EXECUTE,

	##### Wiki events
	'EVENT_WIKI_INIT'					=> EVENT_TYPE_FIRST,
	'EVENT_WIKI_LINK_BUG'				=> EVENT_TYPE_FIRST,
	'EVENT_WIKI_LINK_PROJECT'			=> EVENT_TYPE_FIRST,

) );

