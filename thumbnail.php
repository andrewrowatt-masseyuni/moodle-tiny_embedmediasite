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

/**
 * Thumbnail proxy for Mediasite presentations
 *
 * This file acts as a proxy to load thumbnail images from Mediasite.
 * It fetches the thumbnail using Mediasite API credentials and returns
 * the image with the correct content type.
 *
 * @package    tiny_embedmediasite
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

use tiny_embedmediasite\util;

require_login();

$presentationid = required_param('id', PARAM_ALPHANUMEXT);

// Get Mediasite configuration.
$basemediasiteurl = get_config(util::M_COMPONENT, 'basemediasiteurl');
$sfapikey = get_config(util::M_COMPONENT, 'sfapikey');
$authorization = get_config(util::M_COMPONENT, 'authorization');

if (empty($basemediasiteurl) || empty($sfapikey) || empty($authorization)) {
    http_response_code(500);
    die('Mediasite configuration is incomplete');
}

// Call the Mediasite API to get the thumbnail information.
$endpoint = "https://{$basemediasiteurl}/Api/v1/Presentations('" . urlencode($presentationid) . "')/ThumbnailContent";

$ch = new curl();
$ch->setHeader([
    'Content-Type: application/json',
    "Authorization: {$authorization}",
    'Accept: application/json',
    "sfapikey: {$sfapikey}",
]);

$responseraw = $ch->get($endpoint);

if ($ch->get_errno() !== 0) {
    http_response_code(500);
    die('Failed to fetch thumbnail information from Mediasite API');
}

$info = $ch->get_info();

if ($info['http_code'] != 200) {
    http_response_code($info['http_code']);
    die('Mediasite API returned error: ' . $info['http_code']);
}

$response = json_decode($responseraw, true);

if (!$response || empty($response['value'][0]['ThumbnailUrl'])) {
    http_response_code(404);
    die('Thumbnail not found');
}

$thumbnailurl = $response['value'][0]['ThumbnailUrl'];
$contentmimetype = $response['value'][0]['ContentMimeType'] ?? 'image/jpeg';

// Validate that the content type is an image to prevent content-type injection.
// Note: SVG is excluded to prevent XSS attacks as SVG files can contain executable JavaScript.
$allowedmimetypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($contentmimetype, $allowedmimetypes)) {
    http_response_code(400);
    die('Invalid content type');
}

// Fetch the actual thumbnail image using Mediasite API credentials.
$imagech = new curl();
$imagech->setHeader([
    "Authorization: {$authorization}",
    "sfapikey: {$sfapikey}",
]);

$imagedata = $imagech->get($thumbnailurl);

if ($imagech->get_errno() !== 0) {
    http_response_code(500);
    die('Failed to fetch thumbnail image');
}

$imageinfo = $imagech->get_info();

if ($imageinfo['http_code'] != 200) {
    http_response_code($imageinfo['http_code']);
    die('Failed to fetch thumbnail image: ' . $imageinfo['http_code']);
}

// Return the image with the correct content type.
header('Content-Type: ' . $contentmimetype);
header('Content-Length: ' . strlen($imagedata));
header('Cache-Control: private, max-age=3600'); // Private cache for 1 hour.

echo $imagedata;
