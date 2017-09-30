<?php

// Plugin : Top Poster 1.0
// Author : Harshit Shrivastava
// 2016-2017

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
$plugins->add_hook("postbit", "topposter_postbit");

function topposter_info()
{
	return array(
		"name"			=> "Top Poster",
		"description"	=> "Show Top Poster",
		"website"		=> "http://mybb.com",
		"author"		=> "Harshit Shrivastava",
		"authorsite"	=> "mailto:harshit_s21@rediffmail.com",
		"version"		=> "1.0",
		"compatibility" => "18*"
	);
}
function topposter_postbit(&$post){
	global $mybb,$db,$pid,$lang;
	if ($mybb->settings['topposter_enable'] == 1){
		$lang->load("topposter");
		$show_mode = $mybb->settings['topposter_showmode'] == 'week' ? 'DATE_ADD(CURDATE(), INTERVAL(-WEEKDAY(CURDATE())) DAY)' : ($mybb->settings['topposter_showmode'] == 'month' ? 'CAST(DATE_FORMAT(NOW() ,\'%Y-%m-01\') as DATE)' : ($mybb->settings['topposter_showmode'] == 'year' ? 'CAST(DATE_FORMAT(NOW() ,\'%Y-01-01\') as DATE)':'DATE_ADD(CURDATE(), INTERVAL(-WEEKDAY(CURDATE())) DAY)'));
		$msg = $mybb->settings['topposter_showmode'] == 'week' ? $lang->topposter_week : ($mybb->settings['topposter_showmode'] == 'month' ? $lang->topposter_month : ($mybb->settings['topposter_showmode'] == 'year' ? $lang->topposter_year:$lang->topposter_week));
		$query = $db->simple_select("posts","uid","FROM_UNIXTIME(dateline, '%Y-%m-%d') >= ".$show_mode." group by username ORDER BY COUNT(username) DESC limit 0,1");
		$record = $db->fetch_array($query);
		if (strpos($mybb->settings['topposter_msg'], '{message}') !== false)
			$msg = str_replace('{message}',$msg,$mybb->settings['topposter_msg']);
		if($post['uid'] == $record['uid'])
			$post['groupimage'] .= $msg.' <img class="buddy_status" src="'.$mybb->settings['topposter_url'].'" height="15" weight="15" />';
	}
}
function topposter_activate()
{
global $db;
$topposter_group = array(
        'gid'    => 'NULL',
        'name'  => 'topposter',
        'title'      => 'Top Poster',
        'description'    => 'Show top poster',
        'disporder'    => "1",
        'isdefault'  => "0",
    ); 
$db->insert_query('settinggroups', $topposter_group);
$gid = $db->insert_id(); 
// Enable / Disable
$topposter_setting1 = array(
        'sid'            => 'NULL',
        'name'        => 'topposter_enable',
        'title'            => 'Enable on board',
        'description'    => 'If you set this option to yes, this plugin will hide content from the posts.',
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 1,
        'gid'            => intval($gid),
    );
$topposter_setting2 = array(
        'sid'            => 'NULL',
        'name'        => 'topposter_url',
        'title'            => 'Enter Image URL',
        'description'    => '',
        'optionscode'    => 'text',
        'value'        => '',
        'disporder'        => 2,
        'gid'            => intval($gid),
    );
$topposter_setting3 = array(
        'sid'            => 'NULL',
        'name'        => 'topposter_msg',
        'title'            => 'Message Style',
        'description'    => '',
        'optionscode'    => 'text',
        'value'        => '<strong>{message}</strong>',
        'disporder'        => 3,
        'gid'            => intval($gid),
    );
$topposter_setting4 = array(
        'sid'            => 'NULL',
        'name'        => 'topposter_showmode',
        'title'            => 'Show top poster mode',
        'description'    => 'Select the mode',
        'optionscode'    => 'select
week=Poster of the week
month=Poster of the month
year=Poster of the year
',
        'value'        => '1',
        'disporder'        => 4,
        'gid'            => intval($gid),
    );
$db->insert_query('settinggroups', $topposter_group);
$gid = $db->insert_id(); 
$db->insert_query('settings', $topposter_setting1);
$db->insert_query('settings', $topposter_setting2);
$db->insert_query('settings', $topposter_setting3);
$db->insert_query('settings', $topposter_setting4);
  rebuild_settings();
}
function topposter_deactivate()
{
  global $db;
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'topposter_enable'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'topposter_url'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'topposter_msg'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'topposter_showmode'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='topposter'");
  rebuild_settings();
}
?>
