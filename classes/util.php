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

namespace tiny_embedmediasite;

use curl;
use core\exception\moodle_exception;

/**
 * Class util
 *
 * @package    tiny_embedmediasite
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /**
     * Component name, db tables, strings that are fixed and used around the plugin
     */
    public const M_COMPONENT = 'tiny_embedmediasite';

    /**
     * Holds the default page size
     *
     * @var int
     */
    private const MEDIASITE_API_PAGE_SIZE = 10;

    /**
     * Transforms the list of presentations from the Mediasite for use by the Resposity API
     *
     * @param int $page
     * @return array{list: array, manage?: string, nologin: bool, norefresh: bool, nosearch: bool, page: int, pages: int}
     */
    public static function get_mediasite_presentations(int $page): array {
        global $CFG, $OUTPUT;

        $basemediasiteurl = get_config(self::M_COMPONENT, 'basemediasiteurl');

        $presentations = self::get_presentations($page);

        $list = [];

        foreach ($presentations['value'] as $presentation) {
            // Process each presentation as needed.
            $duration = $presentation['Duration'] ?? 0;

            $listitem = [
                'title' => $presentation['Title'],
                'source' => 'https://' . $basemediasiteurl . '/Play/' . $presentation['Id'],
                'date' => strtotime($presentation['CreationDate']),
                'date_formatted' => userdate(
                    strtotime($presentation['CreationDate']),
                    get_string('strftimedatetime', 'langconfig')
                ),
                'author' => $presentation['Creator'],
                // Using video/mpeg as video/mp4 serves up a Adobe Flash icon in Boost.
                'icon' => $OUTPUT->image_url(file_mimetype_icon('video/mpeg'))->out(false),
                "thumbnail_width" => 400,
                "thumbnail_height" => 400,
                'mimetype' => 'Video',
                'duration' => $duration,
                'duration_formatted' => $duration > 0 ? self::format_duration($duration) : '',
                'description' => $presentation['Description'] ?? '',
            ];

            if (!empty($presentation['ThumbnailUrl'])) {
                $listitem['thumbnail'] = $CFG->wwwroot . '/lib/editor/tiny/plugins/embedmediasite/thumbnail.php?id=' .
                $presentation['Id'];
            }

            $list[] = $listitem;
        }

        $result = [
            'list' => $list,
        ];

        // Add manage URL if set.
        $manageurl = get_config(self::M_COMPONENT, 'manageurl');
        if (!empty($manageurl)) {
            $result['manage'] = 'https://' . $manageurl;
        }

        $result['nologin'] = true;
        $result['norefresh'] = true;
        $result['nosearch'] = true;
        $result['page'] = $page;
        $result['pages'] = ceil($presentations['odata.count'] / self::MEDIASITE_API_PAGE_SIZE);

        return $result;
    }

    /**
     * Use the Mediasite API to get a list of presentations for the current user.
     *
     * @param int $page
     * @throws moodle_exception
     * @return array
     */
    private static function get_presentations(int $page): array {
        global $USER;

        $basemediasiteurl = get_config(self::M_COMPONENT, 'basemediasiteurl');
        $sfapikey = get_config(self::M_COMPONENT, 'sfapikey');
        $authorization = get_config(self::M_COMPONENT, 'authorization');

        $skip = ($page - 1) * self::MEDIASITE_API_PAGE_SIZE; // Page is one-based.

        $orderby = urlencode('CreationDate desc');
        $filter = urlencode("Creator eq '{$USER->username}'");

        $endpoint = "https://$basemediasiteurl" .
            "/Api/v1/Presentations?\$select=full&\$orderby=$orderby&\$top="
            . self::MEDIASITE_API_PAGE_SIZE .
            "&\$skip=$skip&\$filter=$filter";

        $ch = new curl();
        $ch->setHeader([
            'Content-Type: application/json',
            "Authorization: {$authorization}",
            'Accept: application/json',
            "sfapikey: {$sfapikey}",
        ]);

        $responseraw = $ch->get($endpoint);

        if ($ch->get_errno() !== 0) {
            throw new moodle_exception('mediasiteapierror', self::M_COMPONENT, '', $ch->get_errno(), $endpoint);
        }

        $info = $ch->get_info();

        if ($info['http_code'] != 200) {
            throw new moodle_exception('mediasiteapierror', self::M_COMPONENT, '', $info['http_code'], 2);
        }

        $response = json_decode($responseraw, true);

        if (!$response) {
            throw new moodle_exception(
                'mediasiteapierror',
                self::M_COMPONENT,
                '',
                'Invalid JSON response',
                'Invalid JSON response'
            );
        }

        return $response;
    }

    /**
     * Format duration from milliseconds to human-readable format
     *
     * @param int $milliseconds Duration in milliseconds
     * @return string Formatted duration string (e.g., "4 Minutes 35 Seconds")
     */
    private static function format_duration(int $milliseconds): string {
        if ($milliseconds <= 0) {
            return '';
        }

        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);

        $seconds %= 60;
        $minutes %= 60;

        $parts = [];

        if ($hours > 0) {
            $label = $hours == 1 ? 'duration_hour' : 'duration_hours';
            $parts[] = $hours . ' ' . get_string($label, self::M_COMPONENT);
        }

        if ($minutes > 0) {
            $label = $minutes == 1 ? 'duration_minute' : 'duration_minutes';
            $parts[] = $minutes . ' ' . get_string($label, self::M_COMPONENT);
        }

        if ($seconds > 0) {
            $label = $seconds == 1 ? 'duration_second' : 'duration_seconds';
            $parts[] = $seconds . ' ' . get_string($label, self::M_COMPONENT);
        }

        return implode(' ', $parts);
    }
}
