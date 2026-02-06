<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Web service that exposes Mediasite presentation listing to AJAX callers.
 *
 * @package    tiny_embedmediasite
 */

namespace tiny_embedmediasite\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use tiny_embedmediasite\mediasite_client;

// Ensure curl class is available outside of web context.
global $CFG;
require_once($CFG->dirroot . '/lib/filelib.php');

class get_presentations extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, 'Which page of results to retrieve'),
        ]);
    }

    public static function execute(int $page): array {
        $validated = self::validate_parameters(self::execute_parameters(), ['page' => $page]);
        self::validate_context(\context_system::instance());
        return mediasite_client::create()->load_page($validated['page']);
    }

    public static function execute_returns(): external_single_structure {
        $item_shape = new external_single_structure([
            'title'              => new external_value(PARAM_TEXT, 'Name of the presentation'),
            'source'             => new external_value(PARAM_URL, 'Playback URL'),
            'date'               => new external_value(PARAM_INT, 'Creation epoch'),
            'date_formatted'     => new external_value(PARAM_TEXT, 'Formatted creation date'),
            'author'             => new external_value(PARAM_TEXT, 'Owner name'),
            'icon'               => new external_value(PARAM_URL, 'Mimetype icon path', VALUE_OPTIONAL),
            'thumbnail'          => new external_value(PARAM_URL, 'Thumbnail proxy link', VALUE_OPTIONAL),
            'thumbnail_width'    => new external_value(PARAM_INT, 'Thumb width px', VALUE_OPTIONAL),
            'thumbnail_height'   => new external_value(PARAM_INT, 'Thumb height px', VALUE_OPTIONAL),
            'mimetype'           => new external_value(PARAM_TEXT, 'Media type label'),
            'duration'           => new external_value(PARAM_INT, 'Length in ms'),
            'duration_formatted' => new external_value(PARAM_TEXT, 'Readable length'),
        ]);

        return new external_single_structure([
            'list'      => new external_multiple_structure($item_shape),
            'manage'    => new external_value(PARAM_URL, 'Portal link', VALUE_OPTIONAL),
            'nologin'   => new external_value(PARAM_BOOL, 'Login not needed flag'),
            'norefresh' => new external_value(PARAM_BOOL, 'Refresh not needed flag'),
            'nosearch'  => new external_value(PARAM_BOOL, 'Search not available flag'),
            'page'      => new external_value(PARAM_INT, 'Current window index'),
            'pages'     => new external_value(PARAM_INT, 'Total windows'),
        ]);
    }
}
