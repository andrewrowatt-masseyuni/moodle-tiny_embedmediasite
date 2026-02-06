<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Admin settings for the Embed Mediasite TinyMCE plugin.
 *
 * @package    tiny_embedmediasite
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $page = new admin_settingpage('tiny_embedmediasite_settings',
        new lang_string('configplugin', 'tiny_embedmediasite'));

    $page->add(new admin_setting_heading('tiny_embedmediasite/heading',
        '', new lang_string('information', 'tiny_embedmediasite')));

    $page->add(new admin_setting_configtext('tiny_embedmediasite/basemediasiteurl',
        new lang_string('basemediasiteurl', 'tiny_embedmediasite'),
        '', '', PARAM_URL));

    $page->add(new admin_setting_configtext('tiny_embedmediasite/sfapikey',
        new lang_string('sfapikey', 'tiny_embedmediasite'),
        '', '', PARAM_TEXT));

    $page->add(new admin_setting_configtext('tiny_embedmediasite/authorization',
        new lang_string('authorization', 'tiny_embedmediasite'),
        '', '', PARAM_TEXT));

    $page->add(new admin_setting_configtext('tiny_embedmediasite/manageurl',
        new lang_string('manageurl', 'tiny_embedmediasite'),
        new lang_string('manageurl_help', 'tiny_embedmediasite'),
        '', PARAM_URL));

    $settings = $page;
}
