<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2006-2016 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

function plugin_thold_install () {
	global $config;

	api_plugin_register_hook('thold', 'page_head', 'thold_page_head', 'setup.php');
	api_plugin_register_hook('thold', 'top_header_tabs', 'thold_show_tab', 'includes/tab.php');
	api_plugin_register_hook('thold', 'top_graph_header_tabs', 'thold_show_tab', 'includes/tab.php');
	api_plugin_register_hook('thold', 'config_insert', 'thold_config_insert', 'includes/settings.php');
	api_plugin_register_hook('thold', 'config_arrays', 'thold_config_arrays', 'includes/settings.php');
	api_plugin_register_hook('thold', 'config_form', 'thold_config_form', 'includes/settings.php');
	api_plugin_register_hook('thold', 'config_settings', 'thold_config_settings', 'includes/settings.php');
	api_plugin_register_hook('thold', 'draw_navigation_text', 'thold_draw_navigation_text', 'includes/settings.php');
	api_plugin_register_hook('thold', 'data_sources_table', 'thold_data_sources_table', 'setup.php');
	api_plugin_register_hook('thold', 'graphs_new_top_links', 'thold_graphs_new', 'setup.php');
	api_plugin_register_hook('thold', 'api_device_save', 'thold_api_device_save', 'setup.php');
	api_plugin_register_hook('thold', 'update_host_status', 'thold_update_host_status', 'includes/polling.php');
	api_plugin_register_hook('thold', 'poller_output', 'thold_poller_output', 'includes/polling.php');
	api_plugin_register_hook('thold', 'device_action_array', 'thold_device_action_array', 'setup.php');
	api_plugin_register_hook('thold', 'device_action_execute', 'thold_device_action_execute', 'setup.php');
	api_plugin_register_hook('thold', 'device_action_prepare', 'thold_device_action_prepare', 'setup.php');
	api_plugin_register_hook('thold', 'host_edit_bottom', 'thold_host_edit_bottom', 'setup.php');

	api_plugin_register_hook('thold', 'user_admin_setup_sql_save', 'thold_user_admin_setup_sql_save', 'setup.php');
	api_plugin_register_hook('thold', 'poller_bottom', 'thold_poller_bottom', 'includes/polling.php');
	api_plugin_register_hook('thold', 'user_admin_edit', 'thold_user_admin_edit', 'setup.php');
	api_plugin_register_hook('thold', 'rrd_graph_graph_options', 'thold_rrd_graph_graph_options', 'setup.php');
	api_plugin_register_hook('thold', 'graph_buttons', 'thold_graph_button', 'setup.php');

	api_plugin_register_hook('thold', 'snmpagent_cache_install', 'thold_snmpagent_cache_install', 'setup.php');

	/* hooks to add dropdown to allow the assignment of a cluster resource */
	api_plugin_register_hook('thold', 'data_source_action_array', 'thold_data_source_action_array', 'setup.php');
	api_plugin_register_hook('thold', 'data_source_action_prepare', 'thold_data_source_action_prepare', 'setup.php');
	api_plugin_register_hook('thold', 'data_source_action_execute', 'thold_data_source_action_execute', 'setup.php');
	api_plugin_register_hook('thold', 'graphs_action_array', 'thold_graphs_action_array', 'setup.php');
	api_plugin_register_hook('thold', 'graphs_action_prepare', 'thold_graphs_action_prepare', 'setup.php');
	api_plugin_register_hook('thold', 'graphs_action_execute', 'thold_graphs_action_execute', 'setup.php');

	api_plugin_register_hook('thold', 'device_template_edit', 'thold_device_template_edit', 'setup.php');
	api_plugin_register_hook('thold', 'device_template_top', 'thold_device_template_top', 'setup.php');
	api_plugin_register_hook('thold', 'device_edit_pre_bottom', 'thold_device_edit_pre_bottom', 'setup.php');
	api_plugin_register_hook('thold', 'api_device_new', 'thold_api_device_new', 'setup.php');

	api_plugin_register_realm('thold', 'thold.php', __('Plugin -> Configure Thresholds'), 1);
	api_plugin_register_realm('thold', 'thold_templates.php', __('Plugin -> Configure Threshold Templates'), 1);
	api_plugin_register_realm('thold', 'notify_lists.php', __('Plugin -> Manage Notification Lists'), 1);
	api_plugin_register_realm('thold', 'thold_graph.php,graph_thold.php,thold_view_failures.php,thold_view_normal.php,thold_view_recover.php,thold_view_recent.php,thold_view_host.php', __('Plugin -> View Thresholds'), 1);

	include_once($config['base_path'] . '/plugins/thold/includes/database.php');

	thold_setup_database();
	thold_snmpagent_cache_install();
}

function plugin_thold_uninstall () {
	// Do any extra Uninstall stuff here
	thold_snmpagent_cache_uninstall();
}

function plugin_thold_check_config () {
	// Here we will check to ensure everything is configured
	thold_check_upgrade ();

	return true;

}

function plugin_thold_upgrade () {
	// Here we will upgrade to the newest version
	thold_check_upgrade ();
	return false;
}

function thold_version () {
	return plugin_thold_version();
}

function thold_check_upgrade () {
	global $config;
	// Let's only run this check if we are on a page that actually needs the data
	$files = array('thold.php', 'thold_graph.php', 'thold_templates.php', 'poller.php');
	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files))
		return;
	$current = plugin_thold_version ();
	$current = $current['version'];
	$old = read_config_option('plugin_thold_version', TRUE);
	if ($current != $old) {
		include_once($config['base_path'] . '/plugins/thold/includes/database.php');
		thold_upgrade_database ();
	}
}

function thold_check_dependencies() {
	return true;
}

function plugin_thold_check_strict () {
	$mode = db_fetch_cell("select @@global.sql_mode", false);
	if (stristr($mode, 'strict') !== FALSE) {
		return false;
	}
	return true;
}

function plugin_thold_version () {
	return array(
		'name'		=> 'thold',
		'version' 	=> '1.0',
		'longname'	=> 'Thresholds',
		'author'	=> 'Jimmy Conner',
		'homepage'	=> 'http://docs.cacti.net/plugin:thold',
		'email'		=> 'jimmy@sqmail.org',
		'url'		=> 'http://docs.cacti.net/plugin:thold'
	);
}

function thold_graph_button($data) {
	global $config;

	$local_graph_id = $data[1]['local_graph_id'];
	$rra_id = $data[1]['rra'];
	if (isset_request_var('view_type') && !isempty_request_var('view_type')) {
		$view_type = get_request_var('view_type');
	} else {
		set_request_var('view_type', '');
		$view_type = read_config_option('dataquery_type');
	}

	if (isset_request_var('graph_start') && !isempty_request_var('graph_start')) {
		$start = get_request_var('graph_start');
	} else {
		set_request_var('graph_start', '');
		$start = time() - 3600;
	}

	if (isset_request_var('graph_end') && !isempty_request_var('graph_end')) {
		$end = get_request_var('graph_end');
	} else {
		set_request_var('graph_end', '');
		$end = time();
	}

	if (!isset($_SESSION['sess_config_array']['thold_draw_vrules'])) {
		$_SESSION['sess_config_array']['thold_draw_vrules'] = 'off';
	}

	$url = $_SERVER['REQUEST_URI'];
	$url = str_replace('&thold_vrule=on', '', $url);
	$url = str_replace('&thold_vrule=off', '', $url);

	if (!substr_count($url, '?')) {
		$separator = '?';
	}else{
		$separator = '&';
	}

	if (api_user_realm_auth('thold_graph.php')) {
		print '<a class="hyperLink" href="' .  $url . $separator . 'thold_vrule=' . ($_SESSION['sess_config_array']['thold_draw_vrules'] == 'on' ? 'off' : 'on') . '"><img src="' . $config['url_path'] . 'plugins/thold/images/reddot.png" border="0" alt="" title="' . __('Toggle Threshold VRULES %s', ($_SESSION['sess_config_array']['thold_draw_vrules'] == 'on' ? __('Off') : __('On'))) . '" style="padding: 3px;"></a><br>';
	}
	// Add Threshold Creation button
	if (api_user_realm_auth('thold.php')) {
		if (isset_request_var('tree_id')) {
			get_filter_request_var('tree_id');
		}
		if (isset_request_var('leaf_id')) {
			get_filter_request_var('leaf_id');
		}

		print '<a href="' . htmlspecialchars($config['url_path'] . 'plugins/thold/thold.php?action=add' . '&usetemplate=1&local_graph_id=' . $local_graph_id) . '"><img src="' . $config['url_path'] . 'plugins/thold/images/edit_object.png" border="0" alt="" title="' . __('Create Threshold') . '" style="padding: 3px;"></a><br>';
	}
}

function thold_multiexplode ($delimiters, $string) {
	$ready = str_replace($delimiters, $delimiters[0], $string);
	return  @explode($delimiters[0], $ready);
}

function thold_rrd_graph_graph_options ($g) {
	/* handle thold replacement variables */
	$needles      = array();
	$replacements = array();

	/* map the data_template_rrd_id's to the datasource names */
	$defs = explode("\\\n", $g['graph_defs'], -1);
	if (is_array($defs)) {
		foreach ($defs as $def) {
			if (!substr_count($def, 'CDEF') && !substr_count($def, 'VDEF')) {
				$ddef   = thold_multiexplode(array('"', "'"), array($def));
				$kdef   = explode(':', $def);
				$dsname = $kdef[2];
				$temp1  = str_replace('.rrd', '', basename($ddef[1]));
				if (substr_count(basename($ddef[1]), '_') == 0) {
					$local_data_id = $temp1;
				}else{
					$temp2 = explode('_', $temp1);
					$local_data_id = $temp2[sizeof($temp2)-1];
				}
				$data_template_rrd[$dsname] = $local_data_id;
			}
		}
	}

	/* look for any variables to replace */
	$txt_items = explode("\\\n", $g['txt_graph_items']);
	foreach ($txt_items as $item) {
		if (substr_count($item, '|thold')) {
			preg_match("/\|thold\\\:(hi|low)\\\:(.+)\|/", $item, $matches);

			if (count($matches) == 3) {
				$needles[] = $matches[0];
				$data_source = explode('|', $matches[2]);

				/* look up the data_id from the data source name and data_template_rrd */
				$data_id = db_fetch_cell("SELECT id 
					FROM data_template_rrd 
					WHERE local_data_id='" . $data_template_rrd[$data_source[0]] . "' 
					AND data_source_name='" . $data_source[0] . "'");

				$thold_type = db_fetch_cell("SELECT thold_type 
					FROM thold_data 
					WHERE thold_enabled='on' 
					AND data_id='" . $data_id . "'");

				/* fetch the value from thold */
				if ($thold_type == '') {
					$value = '';
				}elseif ($thold_type == 0 || $thold_type == 1) { // Hi/Low & Baseline
					$value = db_fetch_cell('SELECT thold_' . $matches[1] . " FROM thold_data WHERE data_id='" . $data_id . "'");
				}elseif ($thold_type == 1) {  // Time Based
					$value = db_fetch_cell('SELECT time_' . $matches[1] . " FROM thold_data WHERE data_id='" . $data_id . "'");
				}

				//cacti_log('H/L:' . $matches[1] . ', Data ID:' . $data_id . ', Data Source:' . $data_source[0] . ', Remainder:' . $matches[2] . ', Value:' . $value, false);

				if ($value == '' || !is_numeric($value)) {
					$replacements['|thold\:' . $matches[1] . '\:' . $data_source[0] . '|'] = 'strip';
				} else {
					$replacements['|thold\:' . $matches[1] . '\:' . $data_source[0] . '|'] = $value;
				}
			}

			preg_match("/\|thold\\\:(warning_hi|warning_low)\\\:(.+)\|/", $item, $matches);

			if (count($matches) == 3) {
				$needles[] = $matches[0];
				$data_source = explode('|', $matches[2]);

				/* look up the data_id from the data source name and data_template_rrd_id */
				$data_id = db_fetch_cell("SELECT id 
					FROM data_template_rrd 
					WHERE local_data_id='" . $data_template_rrd[$data_source[0]] . "' 
					AND data_source_name='" . $data_source[0] . "'");

				$thold_type = db_fetch_cell("SELECT thold_type 
					FROM thold_data 
					WHERE thold_enabled='on' 
					AND data_template_rrd_id='" . $data_id . "'");

				/* fetch the value from thold */
				if ($thold_type == '') {
					$value = '';
				}elseif ($thold_type == 0 || $thold_type == 1) { // Hi/Low & Baseline
					$value = db_fetch_cell('SELECT thold_' . $matches[1] . " FROM thold_data WHERE data_template_rrd_id='" . $data_id . "'");
				}elseif ($thold_type == 1) { // Time Based
					$value = db_fetch_cell('SELECT time_' . $matches[1] . " FROM thold_data WHERE data_template_rrd_id='" . $data_id . "'");
				}

				//cacti_log('H/L:' . $matches[1] . ', Data ID:' . $data_id . ', Data Source:' . $data_source[0] . ', Remainder:' . $matches[2] . ', Value:' . $value, false);

				if ($value == '' || !is_numeric($value)) {
					$replacements['|thold\:' . $matches[1] . '\:' . $data_source[0] . '|'] = 'strip';
				} else {
					$replacements['|thold\:' . $matches[1] . '\:' . $data_source[0] . '|'] = $value;
				}
			}
		}
	}

	// do we have any needles to replace?
	$i = 0;
	$unsets = array();
	if (is_array($replacements)) {
		foreach($txt_items as $item) {
			foreach($replacements as $key => $replace) {
				//cacti_log('Key:' . $key . ', Replace:' . $replace, false);
				if (substr_count($item, $key)) {
					if ($replace == 'strip') {
						$unsets[] = $i;
					}else{
						$txt_items[$i] = str_replace($key, $replace, $item);
					}
				}
			}
			$i++;
		}

		if (sizeof($unsets)) {
			foreach($unsets as $i) {
				unset($txt_items[$i]);
			}
		}

		$g['txt_graph_items'] = implode("\\\n", $txt_items);
	}

	if (read_config_option('thold_draw_vrules') != 'on') {
		return $g;
	}

	$id = $g['local_graph_id'];

	$end = $g['end'];
	if ($end < 0)
		$end = time() + $end;
	$end++;

	$start = $g['start'];
	if ($start < 0)
		$start = $end + $start;
	$start--;

	if ($id) {
		$rows = db_fetch_assoc_prepared('SELECT time, status FROM plugin_thold_log WHERE local_graph_id = ? AND type = 0 and time > ? and time < ?', array($id, $start, $end));
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$g['graph_defs'] .= 'VRULE:' . $row['time'] . ($row['status'] == 0 ? '#00FF21' : '#FF0000') . ' \\' . "\n";
			}
		}
	}

	return $g;
}

function thold_device_action_execute($action) {
	global $config;

	if ($action != 'thold') {
		return $action;
	}

	include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

	$selected_items = sanitize_unserialize_selected_items(get_nfilter_request_var('selected_items'));

	if ($selected_items != false) {
		for ($i=0; ($i < count($selected_items)); $i++) {
			autocreate($selected_items[$i]);
		}
	}

	return $action;
}

function thold_api_device_new($save) {
	global $config;

	include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

	if (read_config_option('thold_autocreate') == 'on') {
		if (!empty($save['id'])) {
			autocreate($save['id']);
		}
	}

	return $save;
}

function thold_device_action_prepare($save) {
	global $host_list;

	if ($save['drp_action'] != 'thold') {
		return $save;
	}

	print "<tr>
		<td colspan='2' class='textArea'>
			<p>" . __('Click \'Continute\' to apply all appropriate Thresholds to these Device(s).') . "</p>
			<ul>" . $save['host_list'] . "</ul>
		</td>
	</tr>";
}

function thold_device_action_array($device_action_array) {
	$device_action_array['thold'] = 'Apply Thresholds';

	return $device_action_array;
}

function thold_api_device_save($save) {
	global $config;

	$result = db_fetch_assoc('SELECT disabled FROM host WHERE id = ' . $save['id']);

	if (!isset($result[0]['disabled'])) {
		return $save;
	}

	include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

	if ($save['disabled'] != $result[0]['disabled']) {
		if ($save['disabled'] == '') {
			$sql = 'UPDATE thold_data SET thold_enabled = "on" WHERE host_id=' . $save['id'];
			plugin_thold_log_changes($save['id'], 'enabled_host');
		} else {
			$sql = 'UPDATE thold_data SET thold_enabled = "off" WHERE host_id=' . $save['id'];
			plugin_thold_log_changes($save['id'], 'disabled_host');
		}
		$result = db_execute($sql);
	}

	if (isset_request_var('thold_send_email')) {
		$save['thold_send_email'] = form_input_validate(get_nfilter_request_var('thold_send_email'), 'thold_send_email', '', true, 3);
	} else {
		$save['thold_send_email'] = form_input_validate('', 'thold_send_email', '', true, 3);
	}

	if (isset_request_var('thold_host_email')) {
		$save['thold_host_email'] = form_input_validate(get_nfilter_request_var('thold_host_email'), 'thold_host_email', '', true, 3);
	} else {
		$save['thold_host_email'] = form_input_validate('', 'thold_host_email', '', true, 3);
	}

	return $save;
}

function thold_user_admin_edit($user) {
	global $fields_user_user_edit_host;

	$value = '';

	if ($user != 0) {
		$value = db_fetch_cell("SELECT data FROM plugin_thold_contacts WHERE user_id = $user AND type = 'email'");
	}

	$fields_user_user_edit_host['email'] = array(
		'method' => 'textbox',
		'value' => $value,
		'friendly_name' => __('Email Address'),
		'form_id' => '|arg1:id|',
		'default' => '',
		'max_length' => 255
	);

	return $user;
}

function thold_data_sources_table($ds) {
	global $config;

	if (!isset($ds['data_source'])) {
		$exists = db_fetch_cell_prepared('SELECT id FROM thold_data WHERE local_data_id = ?', array($ds['local_data_id']));

		if ($exists) {
			$ds['data_template_name'] = "<a title='" . __('Create Threshold from Data Source') . "' class='hyperLink' href='" . htmlspecialchars('plugins/thold/thold.php?action=edit&id=' . $exists) . "'>" . ((empty($ds['data_template_name'])) ? '<em>' . __('None'). '</em>' : htmlspecialchars($ds['data_template_name'], ENT_QUOTES)) . '</a>';
		}else{
			$data_template_id = db_fetch_cell_prepared('SELECT data_template_id FROM data_local WHERE id = ?', array($ds['local_data_id']));

			$ds['data_template_name'] = "<a title='" . __('Create Threshold from Data Source') . "' class='hyperLink' href='" . htmlspecialchars('plugins/thold/thold.php?action=edit&local_data_id=' . $ds['local_data_id'] . '&host_id=' . $ds['host_id'] . '&data_template_id=' . $data_template_id . '&data_template_rrd_id=&local_graph_id=&thold_template_id=0') . "'>" . ((empty($ds['data_template_name'])) ? '<em>' . __('None') . '</em>' : htmlspecialchars($ds['data_template_name'], ENT_QUOTES)) . '</a>';
		}
	} else {
		$ds['template_name'] = "<a title='" . __('Create Threshold from Data Source') . "' class='hyperLink' href='" . htmlspecialchars('plugins/thold/thold.php?local_data_id=' . $ds['data_source']['local_data_id'] . '&host_id=' . $ds['data_source']['host_id'] . '&thold_template_id=0') . "'>" . ((empty($ds['data_source']['data_template_name'])) ? '<em>' . __('None') . '</em>' : htmlspecialchars($ds['data_source']['data_template_name'], ENT_QUOTES)) . '</a>';
	}

	return $ds;
}

function thold_graphs_new() {
	global $config;

	print '<span class="linkMarker">*</span><a class="autocreate hyperLink" href="' . htmlspecialchars($config['url_path'] . 'plugins/thold/thold.php?action=autocreate&host_id=' . get_filter_request_var('host_id')) . '">' . __('Auto-create thresholds'). '</a><br>';
}

function thold_user_admin_setup_sql_save($save) {
	global $database_default, $database_type, $database_port, $database_password, $database_username, $database_hostname, $config;

	if (is_error_message()) {
		return $save;
	}

	if (isset_request_var('email')) {
		$email = form_input_validate(get_nfilter_request_var('email'), 'email', '', true, 3);
		if ($save['id'] == 0) {
			$save['id'] = sql_save($save, 'user_auth');
		}

		$cid = db_fetch_cell("SELECT id FROM plugin_thold_contacts WHERE type = 'email' AND user_id = " . $save['id'], false);

		if ($cid) {
			db_execute("REPLACE INTO plugin_thold_contacts (id, user_id, type, data) VALUES ($cid, " . $save['id'] . ", 'email', '$email')");
		}else{
			db_execute("REPLACE INTO plugin_thold_contacts (user_id, type, data) VALUES (" . $save['id'] . ", 'email', '$email')");
		}
	}

	return $save;
}

function thold_data_source_action_execute($action) {
	global $config, $form_array;

	include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

	if ($action == 'plugin_thold_create') {
		$selected_items = sanitize_unserialize_selected_items(get_nfilter_request_var('selected_items'));

		if ($selected_items != false) {
			$message = '';
			get_filter_request_var('thold_template_id');

			$template = db_fetch_row('SELECT * FROM thold_template WHERE id=' . get_request_var('thold_template_id'));

			for ($i=0;($i<count($selected_items));$i++) {
				$local_data_id    = $selected_items[$i];
				$data_source      = db_fetch_row('SELECT * FROM data_local WHERE id=' . $local_data_id);
				$data_template_id = $data_source['data_template_id'];
				$existing         = db_fetch_assoc('SELECT id FROM thold_data WHERE local_data_id=' . $local_data_id . ' AND data_template_rrd_id=' . $data_template_id);

				if (count($existing) == 0 && count($template)) {
					$rrdlookup = db_fetch_cell("SELECT id
						FROM data_template_rrd
						WHERE local_data_id=$local_data_id
						ORDER BY id
						LIMIT 1");

					$grapharr  = db_fetch_row("SELECT local_graph_id, graph_template_id
						FROM graph_templates_item
						WHERE task_item_id=$rrdlookup
						AND local_graph_id<>''
						LIMIT 1");

					$graph     = (isset($grapharr['local_graph_id']) ? $grapharr['local_graph_id'] : '');

					if ($graph) {
						$data_source_name = $template['data_source_name'];
						$insert = array();

						$name = thold_format_name($template, $graph, $local_data_id, $data_source_name);

						$insert['name']               = $name;
						$insert['host_id']            = $data_source['host_id'];
						$insert['local_data_id']      = $local_data_id;
						$insert['local_graph_id']     = $graph;
						$insert['data_template_id']   = $data_template_id;
						$insert['graph_template_id']  = $grapharr['graph_template_id'];
						$insert['thold_hi']           = $template['thold_hi'];
						$insert['thold_low']          = $template['thold_low'];
						$insert['thold_fail_trigger'] = $template['thold_fail_trigger'];
						$insert['thold_enabled']      = $template['thold_enabled'];
						$insert['bl_ref_time_range']  = $template['bl_ref_time_range'];
						$insert['bl_pct_down']        = $template['bl_pct_down'];
						$insert['bl_pct_up']          = $template['bl_pct_up'];
						$insert['bl_fail_trigger']    = $template['bl_fail_trigger'];
						$insert['bl_alert']           = $template['bl_alert'];
						$insert['repeat_alert']       = $template['repeat_alert'];
						$insert['notify_extra']       = $template['notify_extra'];
						$insert['cdef']               = $template['cdef'];
						$insert['thold_template_id']  = $template['id'];
						$insert['template_enabled']   = 'on';
	
						$rrdlist = db_fetch_assoc("SELECT id, data_input_field_id FROM data_template_rrd where local_data_id='$local_data_id' and data_source_name='$data_source_name'");
	
						$int = array('id', 'data_template_id', 'data_source_id', 'thold_fail_trigger', 'bl_ref_time_range', 'bl_pct_down', 'bl_pct_up', 'bl_fail_trigger', 'bl_alert', 'repeat_alert', 'cdef');
						foreach ($rrdlist as $rrdrow) {
							$data_rrd_id=$rrdrow['id'];
							$insert['data_template_rrd_id'] = $data_rrd_id;
							$existing = db_fetch_assoc("SELECT id FROM thold_data WHERE local_data_id='$local_data_id' AND data_template_rrd_id='$data_rrd_id'");
							if (count($existing) == 0) {
								$insert['id'] = 0;
								$id = sql_save($insert, 'thold_data');
								if ($id) {
									thold_template_update_threshold($id, $insert['thold_template_id']);

									$l = db_fetch_assoc("SELECT name FROM data_template where id=$data_template_id");
									$tname = $l[0]['name'];

									$name = $data_source_name;
									if ($rrdrow['data_input_field_id'] != 0) {
										$l = db_fetch_assoc('SELECT name FROM data_input_fields where id=' . $rrdrow['data_input_field_id']);
										$name = $l[0]['name'];
									}

									plugin_thold_log_changes($id, 'created', " $tname [$name]");

									$message .= __('Created threshold for the Graph \'<i>%s</i>\' using the Data Source \'<i>%s</i>\'', $tname, $name) . "<br>";
								}
							}
						}
					}
				}
			}

			if (strlen($message)) {
				$_SESSION['thold_message'] = "<font size=-2>$message</font>";
			}else{
				$_SESSION['thold_message'] = "<font size=-2>" . __('Threshold(s) Already Exist - No Thresholds Created') . "</font>";
			}
			raise_message('thold_message');
		}
	}

	return $action;
}

function thold_data_source_action_prepare($save) {
	global $config;

	if ($save['drp_action'] == 'plugin_thold_create') {
		/* get the valid thold templates
		 * remove those hosts that do not have any valid templates
		 */
		$templates  = '';
		$found_list = '';
		$not_found  = '';
		if (sizeof($save['ds_array'])) {
		foreach($save['ds_array'] as $item) {
			$data_template_id = db_fetch_cell("SELECT data_template_id FROM data_local WHERE id=$item");

			if ($data_template_id != '') {
				if (sizeof(db_fetch_assoc("SELECT id FROM thold_template WHERE data_template_id=$data_template_id"))) {
					$found_list .= '<li>' . get_data_source_title($item) . '</li>';
					if (strlen($templates)) {
						$templates .= ", $data_template_id";
					}else{
						$templates  = "$data_template_id";
					}
				}else{
					$not_found .= '<li>' . get_data_source_title($item) . '</li>';
				}
			}else{
				$not_found .= '<li>' . get_data_source_title($item) . '</li>';
			}
		}
		}

		if (strlen($templates)) {
			$sql = 'SELECT id, name FROM thold_template WHERE data_template_id IN (' . $templates . ') ORDER BY name';
		}else{
			$sql = 'SELECT id, name FROM thold_template ORDER BY name';
		}

		print "<tr><td colspan='2' class='textArea'>\n";

		if (strlen($found_list)) {
			if (strlen($not_found)) {
				print '<p>' . __('The following Data Sources have no Threshold Templates associated with them') . '</p>';
				print '<ul>' . $not_found . '</ul>';
			}

			print '<p>' . __('Are you sure you wish to create Thresholds for these Data Sources?') . '</p>
					<ul>' . $found_list . "</ul>
				</td>
			</tr></table><table class='cactiTable'>\n";

			$form_array = array(
				'general_header' => array(
					'friendly_name' => __('Available Threshold Templates'),
					'method' => 'spacer',
				),
				'thold_template_id' => array(
					'method' => 'drop_sql',
					'friendly_name' => __('Select a Threshold Template'),
					'description' => '',
					'none_value' => __('None'),
					'value' => __('None'),
					'sql' => $sql
				)
			);

			draw_edit_form(
				array(
					'config' => array('no_form_tag' => true),
					'fields' => $form_array
					)
				);

			print "</tr></table>\n";
		}else{
			if (strlen($not_found)) {
				print '<p>' . __('There are no Threshold Templates associated with the following Data Sources'). '</p>';
				print '<ul>' . $not_found . '</ul>';
			}
		}
	}else{
		return $save;
	}
}

function thold_data_source_action_array($action) {
	$action['plugin_thold_create'] = __('Create Threshold from Template');
	return $action;
}

function thold_graphs_action_execute($action) {
	global $config, $form_array;

	include_once($config['base_path'] . '/plugins/thold/thold_functions.php');

	if ($action == 'plugin_thold_create') {
		$selected_items = sanitize_unserialize_selected_items(get_nfilter_request_var('selected_items'));

		if ($selected_items != false) {
			$message = '';
			get_filter_request_var('thold_template_id');

			$template = db_fetch_row('SELECT * FROM thold_template WHERE id=' . get_request_var('thold_template_id'));

			for ($i=0;($i<count($selected_items));$i++) {
				$graph    = $selected_items[$i];

				$temp = db_fetch_row("SELECT dtr.*
					 FROM data_template_rrd AS dtr
					 LEFT JOIN graph_templates_item AS gti
					 ON gti.task_item_id=dtr.id
					 LEFT JOIN graph_local AS gl
					 ON gl.id=gti.local_graph_id
					 WHERE gl.id=$graph");

				$data_template_id = $temp['data_template_id'];
				$local_data_id = $temp['local_data_id'];

				$data_source      = db_fetch_row('SELECT * FROM data_local WHERE id=' . $local_data_id);
				$data_template_id = $data_source['data_template_id'];
				$existing         = db_fetch_assoc('SELECT id FROM thold_data WHERE local_data_id=' . $local_data_id . ' AND data_template_rrd_id=' . $data_template_id);

				if (count($existing) == 0 && count($template)) {
					if ($graph) {
						$rrdlookup = db_fetch_cell("SELECT id 
							FROM data_template_rrd
							WHERE local_data_id=$local_data_id
							ORDER BY id
							LIMIT 1");

						$grapharr = db_fetch_row("SELECT graph_template_id
							FROM graph_templates_item
							WHERE task_item_id=$rrdlookup
							AND local_graph_id=$graph");

						$data_source_name = $template['data_source_name'];

						$insert = array();

						$name = thold_format_name($template, $graph, $local_data_id, $data_source_name);

						$insert['name']               = $name;
						$insert['host_id']            = $data_source['host_id'];
						$insert['local_data_id']      = $local_data_id;
						$insert['local_graph_id']     = $graph;
						$insert['data_template_id']   = $data_template_id;
						$insert['graph_template_id']  = $grapharr['graph_template_id'];
						$insert['thold_hi']           = $template['thold_hi'];
						$insert['thold_low']          = $template['thold_low'];
						$insert['thold_fail_trigger'] = $template['thold_fail_trigger'];
						$insert['thold_enabled']      = $template['thold_enabled'];
						$insert['bl_ref_time_range']  = $template['bl_ref_time_range'];
						$insert['bl_pct_down']        = $template['bl_pct_down'];
						$insert['bl_pct_up']          = $template['bl_pct_up'];
						$insert['bl_fail_trigger']    = $template['bl_fail_trigger'];
						$insert['bl_alert']           = $template['bl_alert'];
						$insert['repeat_alert']       = $template['repeat_alert'];
						$insert['notify_extra']       = $template['notify_extra'];
						$insert['cdef']               = $template['cdef'];
						$insert['thold_template_id']  = $template['id'];
						$insert['template_enabled']   = 'on';

						$rrdlist = db_fetch_assoc("SELECT id, data_input_field_id FROM data_template_rrd where local_data_id='$local_data_id' and data_source_name='$data_source_name'");

						$int = array('id', 'data_template_id', 'data_source_id', 'thold_fail_trigger', 'bl_ref_time_range', 'bl_pct_down', 'bl_pct_up', 'bl_fail_trigger', 'bl_alert', 'repeat_alert', 'cdef');
						foreach ($rrdlist as $rrdrow) {
							$data_rrd_id=$rrdrow['id'];
							$insert['data_id'] = $data_rrd_id;
							$existing = db_fetch_assoc("SELECT id FROM thold_data WHERE local_data_id='$local_data_id' AND data_template_rrd_id='$data_rrd_id'");
							if (count($existing) == 0) {
								$insert['id'] = 0;
								$id = sql_save($insert, 'thold_data');
								if ($id) {
									thold_template_update_threshold ($id, $insert['template']);

									$l = db_fetch_assoc("SELECT name FROM data_template where id=$data_template_id");
									$tname = $l[0]['name'];

									$name = $data_source_name;
									if ($rrdrow['data_input_field_id'] != 0) {
										$l = db_fetch_assoc('SELECT name FROM data_input_fields where id=' . $rrdrow['data_input_field_id']);
										$name = $l[0]['name'];
									}
									plugin_thold_log_changes($id, 'created', " $tname [$name]");
									$message .= __('Created threshold for the Graph \'<i>%s</i>\' using the Data Source \'<i>%s</i>\'', $tname, $name) . "<br>";
								}
							}
						}
					}
				}
			}

			if (strlen($message)) {
				$_SESSION['thold_message'] = "<font size=-2>$message</font>";
			}else{
				$_SESSION['thold_message'] = "<font size=-2>" . __('Threshold(s) Already Exist - No Thresholds Created') . "</font>";
			}

			raise_message('thold_message');
		}
	}

	return $action;
}

function thold_graphs_action_prepare($save) {
	global $config;

	if ($save['drp_action'] == 'plugin_thold_create') {
		/* get the valid thold templates
		 * remove those hosts that do not have any valid templates
		 */
		$templates  = '';
		$found_list = '';
		$not_found  = '';

		if (sizeof($save['graph_array'])) {
			foreach($save['graph_array'] as $item) {
				$data_template_id = db_fetch_cell("SELECT dtr.data_template_id
					 FROM data_template_rrd AS dtr
					 LEFT JOIN graph_templates_item AS gti
					 ON gti.task_item_id=dtr.id
					 LEFT JOIN graph_local AS gl
					 ON gl.id=gti.local_graph_id
					 WHERE gl.id=$item");

				if ($data_template_id != '') {
					if (sizeof(db_fetch_assoc("SELECT id FROM thold_template WHERE data_template_id=$data_template_id"))) {
						$found_list .= '<li>' . get_graph_title($item) . '</li>';
						if (strlen($templates)) {
							$templates .= ", $data_template_id";
						}else{
							$templates  = "$data_template_id";
						}
					}else{
						$not_found .= '<li>' . get_graph_title($item) . '</li>';
					}
				}else{
					$not_found .= '<li>' . get_graph_title($item) . '</li>';
				}
			}
		}

		if (strlen($templates)) {
			$sql = 'SELECT id, name FROM thold_template WHERE data_template_id IN (' . $templates . ') ORDER BY name';
		}else{
			$sql = 'SELECT id, name FROM thold_template ORDER BY name';
		}

		print "<tr><td colspan='2' class='textArea'>\n";

		if (strlen($found_list)) {
			if (strlen($not_found)) {
				print '<p>' . __('The following Graphs have no Threshold Templates associated with them') . '</p>';
				print '<ul>' . $not_found . '</ul>';
			}

			print '<p>Are you sure you wish to create Thresholds for these Graphs?</p>
				<ul>' . $found_list . "</ul>
				</td>
			</tr>\n";

			$form_array = array(
				'general_header' => array(
					'friendly_name' => __('Available Threshold Templates'),
					'method' => 'spacer',
				),
				'thold_template_id' => array(
					'method' => 'drop_sql',
					'friendly_name' => __('Select a Threshold Template'),
					'description' => '',
					'none_value' => __('None'),
					'value' => __('None'),
					'sql' => $sql
				)
			);

			draw_edit_form(
				array(
					'config' => array('no_form_tag' => true),
					'fields' => $form_array
					)
				);
		}else{
			if (strlen($not_found)) {
				print '<p>' . __('There are no Threshold Templates associated with the following Graphs') . '</p>';
				print '<ul>' . $not_found . '</ul>';
			}
		}
	}else{
		return $save;
	}
}

function thold_graphs_action_array($action) {
	$action['plugin_thold_create'] = __('Create Threshold from Template');
	return $action;
}

function thold_host_edit_bottom() {
	?>
	<script type='text/javascript'>

	changeNotify();
	function changeNotify() {
		if ($('#thold_send_email').val() < 2) {
			$('#row_thold_host_email').hide();
		}else{
			$('#row_thold_host_email').show();
		}
	}

	</script>
	<?php
}

function thold_snmpagent_cache_install() {
	global $config;
	if (class_exists('MibCache')) {
		$mc = new MibCache('CACTI-THOLD-MIB');
		$mc->install($config['base_path'] . '/plugins/thold/CACTI-THOLD-MIB', true);
	}
}

function thold_snmpagent_cache_uninstall(){
	global $config;
	if (class_exists('MibCache')) {
		$mc = new MibCache('CACTI-THOLD-MIB');
		$mc->uninstall();
	}
}

function thold_page_head() {
	global $config;

	if (file_exists($config['base_path'] . "/plugins/thold/themes/" . get_selected_theme() . "/main.css")) {
		print "<link href='" . $config['url_path'] . "plugins/thold/themes/" . get_selected_theme() . "/main.css' type='text/css' rel='stylesheet'>\n";
	}
}

function thold_device_edit_pre_bottom() {
	html_start_box(__('Associated Threshold Templates'), '100%', '', '3', 'center', '');

	$host_template_id = db_fetch_cell_prepared('SELECT host_template_id FROM host WHERE id = ?' ,array(get_request_var('id')));

	$threshold_templates = db_fetch_assoc_prepared('SELECT ptdt.thold_template_id, tt.name
		FROM plugin_thold_host_template AS ptdt
		INNER JOIN thold_template AS tt
		ON tt.id=ptdt.thold_template_id
		WHERE ptdt.host_template_id = ? ORDER BY name', array($host_template_id));

	html_header(array(__('Name'), __('Status')));

	$i = 0;
	if (sizeof($threshold_templates)) {
		foreach ($threshold_templates as $item) {
			$exists = db_fetch_cell_prepared('SELECT id 
				FROM thold_data 
				WHERE host_id = ? 
				AND thold_template_id = ?', 
				array(get_request_var('id'), $item['thold_template_id']));

			if ($exists) {
				$exists = __('Threshold Exists');
			}else{
				$exists = __('Threshold Does Not Exist');
			}

			form_alternate_row("tt$i", true);
			?>
				<td class='left'>
					<strong><?php print $i;?>)</strong> <?php print htmlspecialchars($item['name']);?>
				</td>
				<td>
					<?php print $exists;?>
				</td>
			<?php
			form_end_row();

			$i++;
		}
	}else{ 
		print '<tr><td><em>' . __('No Associated Threshold Templates.') . '</em></td></tr>'; 
	}

	html_end_box();
}

function thold_device_template_edit() {
	html_start_box(__('Associated Threshold Templates'), '100%', '', '3', 'center', '');

	$threshold_templates = db_fetch_assoc_prepared('SELECT ptdt.thold_template_id, tt.name
		FROM plugin_thold_host_template AS ptdt
		INNER JOIN thold_template AS tt
		ON tt.id=ptdt.thold_template_id
		WHERE ptdt.host_template_id = ? ORDER BY name', array(get_request_var('id')));

	$i = 0;
	if (sizeof($threshold_templates)) {
		foreach ($threshold_templates as $item) {
			form_alternate_row("tt$i", true);
			?>
				<td class='left'>
					<strong><?php print $i;?>)</strong> <?php print htmlspecialchars($item['name']);?>
				</td>
				<td class='right'>
					<a class='delete deleteMarker fa fa-remove' title='<?php print __('Delete');?>' href='<?php print htmlspecialchars('host_templates.php?action=item_remove_tt_confirm&id=' . $item['thold_template_id'] . '&host_template_id=' . get_request_var('id'));?>'></a>
				</td>
			<?php
			form_end_row();

			$i++;
		}
	}else{ 
		print '<tr><td><em>' . __('No Associated Threshold Templates.') . '</em></td></tr>'; 
	}

	$unmapped = db_fetch_assoc_prepared('SELECT DISTINCT tt.id, tt.name
		FROM thold_template AS tt
		LEFT JOIN plugin_thold_host_template AS ptdt
		ON tt.id=ptdt.thold_template_id
		WHERE ptdt.host_template_id IS NULL OR ptdt.host_template_id != ?
		ORDER BY tt.name', array(get_request_var('id')));

	if (sizeof($unmapped)) {
		?>
		<tr class='odd'>
			<td colspan='2'>
				<table>
					<tr style='line-height:10px;'>
						<td style='padding-right: 15px;'>
							<?php print __('Add Threshold Template');?>
						</td>
						<td>
							<?php form_dropdown('thold_template_id',$unmapped ,'name','id','','','');?>
						</td>
						<td>
							<input type='button' value='<?php print __('Add');?>' id='add_tt' title='<?php print __('Add Threshold Template to Device Template');?>'>
						</td>
					</tr>
				</table>
				<script type='text/javascript'>
				$('#add_tt').click(function() {
					$.post('host_templates.php?header=false&action=item_add_tt', {
						host_template_id: $('#id').val(),
						thold_template_id: $('#thold_template_id').val(),
						__csrf_magic: csrfMagicToken
					}).done(function(data) {
						$('div[class^="ui-"]').remove();
						$('#main').html(data);
						applySkin();
					});
				});
				</script>
			</td>
		</tr>
		<?php
	}

	html_end_box();
}

function thold_device_template_top() {
	if (get_request_var('action') == 'item_remove_tt_confirm') {
		/* ================= input validation ================= */
		get_filter_request_var('id');
		get_filter_request_var('host_template_id');
		/* ==================================================== */

		form_start('host_templates.php?action=edit&id' . get_request_var('host_template_id'));

		html_start_box('', '100%', '', '3', 'center', '');

		$template = db_fetch_row_prepared('SELECT * FROM thold_template WHERE id = ?', array(get_request_var('id')));

		?>
		<tr>
			<td class='topBoxAlt'>
				<p><?php print __('Click \'Continue\' to delete the following Threshold Template will be disassociated from the Device Template.');?></p>
				<p><?php print __('Threshold Template Name: %s', htmlspecialchars($template['name']));?>'<br>
			</td>
		</tr>
		<tr>
			<td align='right'>
				<input id='cancel' type='button' value='<?php print __('Cancel');?>' onClick='$("#cdialog").dialog("close")' name='cancel'>
				<input id='continue' type='button' value='<?php print __('Continue');?>' name='continue' title='<?php print __('Remove Threshold Template');?>'>
			</td>
		</tr>
		<?php

		html_end_box();

		form_end();

		?>
		<script type='text/javascript'>
		$(function() {
			$('#cdialog').dialog();
		});

	    $('#continue').click(function(data) {
			$.post('host_templates.php?action=item_remove_tt', {
				__csrf_magic: csrfMagicToken,
				host_template_id: <?php print get_request_var('host_template_id');?>,
				id: <?php print get_request_var('id');?>
			}, function(data) {
				$('#cdialog').dialog('close');
				loadPageNoHeader('host_templates.php?action=edit&header=false&id=<?php print get_request_var('host_template_id');?>');
			});
		});
		</script>
		<?php

		exit;
	}elseif (get_request_var('action') == 'item_remove_tt') {
		/* ================= input validation ================= */
		get_filter_request_var('id');
		get_filter_request_var('host_template_id');
		/* ==================================================== */

		db_execute_prepared('DELETE FROM plugin_thold_host_template WHERE thold_template_id = ? AND host_template_id = ?', array(get_request_var('id'), get_request_var('host_template_id')));

		header('Location: host_templates.php?header=false&action=edit&id=' . get_request_var('host_template_id'));

		exit;
	}elseif (get_request_var('action') == 'item_add_tt') {
		/* ================= input validation ================= */
		get_filter_request_var('host_template_id');
		get_filter_request_var('thold_template_id');
		/* ==================================================== */

		db_execute_prepared('REPLACE INTO plugin_thold_host_template
			(host_template_id, thold_template_id) VALUES (?, ?)',
			array(get_request_var('host_template_id'), get_request_var('thold_template_id')));

		header('Location: host_templates.php?header=false&action=edit&id=' . get_request_var('host_template_id'));

		exit;
	}
}

