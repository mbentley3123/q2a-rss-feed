<?php
/*
	Plugin Name: RSS feed widget
	Plugin URI: https://github.com/blaaa-blaaa-blaa
	Plugin Description: Provides an RSS feed in the sidebar
	Plugin Version: 0.1
	Plugin Date: 2013-03-04
	Plugin Author: Mark Bentley
	Plugin Author URI: http://BentleyDesigns.thebentleys.ca/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.4

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
*/

if ( !defined('QA_VERSION') )
{
	header('Location: ../../');
	exit;
}

qa_register_plugin_module('widget', 'qa-rss-feed.php', 'qa_rss_feed', 'RSS Feed');
