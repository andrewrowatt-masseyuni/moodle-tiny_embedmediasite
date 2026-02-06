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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tiny_embedmediasite\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_api;
use core_external\external_value;

// phpcs:ignoreFile moodle.Files.MoodleInternal.MoodleInternalGlobalState
/* To resolved the Exception - Class "curl" not found issue */
require_once($CFG->dirroot . '/lib/filelib.php');

/**
 * Implementation of web service tiny_embedmediasite_get_presentations
 *
 * @package    tiny_embedmediasite
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_presentations extends external_api {
    /**
     * Describes the parameters for tiny_embedmediasite_get_presentations
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, 'Page number'),
        ]);
    }

    /**
     * Implementation of web service tiny_embedmediasite_get_presentations
     *
     * @param int $page
     */
    public static function execute($page) {
        // Parameter validation.
        ['page' => $page] = self::validate_parameters(
            self::execute_parameters(),
            ['page' => $page]
        );

        // From web services we don't call require_login(), but rather validate_context.
        $context = \context_system::instance();
        self::validate_context($context);

        $presentations = \tiny_embedmediasite\util::get_mediasite_presentations($page);

        return $presentations;
    }

    /**
     * Describe the return structure for tiny_embedmediasite_get_presentations
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'list' => new external_multiple_structure(
                new external_single_structure([
                    'title' => new external_value(PARAM_TEXT, 'Presentation title'),
                    'source' => new external_value(PARAM_URL, 'Presentation source URL'),
                    'date' => new external_value(PARAM_INT, 'Presentation creation date'),
                    'date_formatted' => new external_value(PARAM_TEXT, 'Presentation creation date formatted'),
                    'author' => new external_value(PARAM_TEXT, 'Presentation author'),
                    'mimetype' => new external_value(PARAM_TEXT, 'Presentation mimetype'),
                    'duration' => new external_value(PARAM_INT, 'Presentation duration in milliseconds'),
                    'duration_formatted' => new external_value(PARAM_TEXT, 'Presentation duration formatted'),
                    'description' => new external_value(PARAM_TEXT, 'Presentation description'),
                    'parentfoldername' => new external_value(PARAM_TEXT, 'Presentation parent folder name'),
                    'id' => new external_value(PARAM_TEXT, 'Presentation ID'),
                    'thumbnail' => new external_value(PARAM_URL, 'Presentation thumbnail URL', VALUE_OPTIONAL),
                ])
            ),
            'manage' => new external_value(PARAM_URL, 'Management URL', VALUE_OPTIONAL),
            'nologin' => new external_value(PARAM_BOOL, 'No login required'),
            'norefresh' => new external_value(PARAM_BOOL, 'No refresh'),
            'nosearch' => new external_value(PARAM_BOOL, 'No search'),
            'page' => new external_value(PARAM_INT, 'Current page number'),
            'pages' => new external_value(PARAM_INT, 'Total pages'),
        ]);
    }
}
