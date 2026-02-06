<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Admin settings for the Embed Mediasite TinyMCE plugin.
 *
 * @package    tiny_embedmediasite
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $page = new admin_settingpage(
        'tiny_embedmediasite_settings',
        new lang_string('configplugin', 'tiny_embedmediasite')
    );

    $page->add(new admin_setting_heading(
        'tiny_embedmediasite/heading',
        '',
        new lang_string('information', 'tiny_embedmediasite')
    ));

    $page->add(new admin_setting_configtext(
        'tiny_embedmediasite/basemediasiteurl',
        new lang_string('basemediasiteurl', 'tiny_embedmediasite'),
        '',
        '',
        PARAM_URL
    ));

    $page->add(new admin_setting_configtext(
        'tiny_embedmediasite/sfapikey',
        new lang_string('sfapikey', 'tiny_embedmediasite'),
        '',
        '',
        PARAM_TEXT
    ));

    $page->add(new admin_setting_configtext(
        'tiny_embedmediasite/authorization',
        new lang_string('authorization', 'tiny_embedmediasite'),
        '',
        '',
        PARAM_TEXT
    ));

    $page->add(new admin_setting_configtext(
        'tiny_embedmediasite/manageurl',
        new lang_string('manageurl', 'tiny_embedmediasite'),
        new lang_string('manageurl_help', 'tiny_embedmediasite'),
        '',
        PARAM_URL
    ));

    $settings = $page;
}
