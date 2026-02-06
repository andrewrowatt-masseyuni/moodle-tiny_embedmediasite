<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Proxies thumbnail images from the Mediasite API so that browser
 * requests never need direct access to API credentials.
 *
 * @package    tiny_embedmediasite
 */

// This script lives at lib/editor/tiny/plugins/embedmediasite/ inside Moodle.
require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

/**
 * Terminate the request with an HTTP error.
 *
 * @param int    $code HTTP status code.
 * @param string $msg  Human-readable reason.
 * @return never
 */
function bail(int $code, string $msg): void {
    http_response_code($code);
    echo $msg;
    exit;
}

/**
 * Read a plugin setting or bail on missing config.
 *
 * @param string $key Setting name.
 * @return string The value.
 */
function need_setting(string $key): string {
    $val = get_config('tiny_embedmediasite', $key);
    if ($val === false || $val === '') {
        bail(500, "Missing configuration: {$key}");
    }
    return $val;
}

/**
 * Make an authenticated GET to the Mediasite API.
 *
 * @param string $endpoint Full URL.
 * @param string $authval  Authorization header content.
 * @param string $apikeyval sfapikey header content.
 * @return array{body: string, info: array} Response body and curl info.
 */
function api_get(string $endpoint, string $authval, string $apikeyval): array {
    $transport = new curl();
    $transport->setHeader([
        'Content-Type: application/json',
        'Authorization: ' . $authval,
        'Accept: application/json',
        'sfapikey: ' . $apikeyval,
    ]);
    $body = $transport->get($endpoint);
    if ($transport->get_errno()) {
        bail(502, 'Upstream request failed');
    }
    return ['body' => $body, 'info' => $transport->get_info()];
}

// --- Main flow ---

$entryid   = required_param('id', PARAM_ALPHANUMEXT);
$serverurl = need_setting('basemediasiteurl');
$authval   = need_setting('authorization');
$apikeyval = need_setting('sfapikey');

// Step 1: ask the API for the thumbnail metadata of this presentation.
$metaurl = 'https://' . rtrim($serverurl, '/')
    . "/Api/v1/Presentations('" . urlencode($entryid) . "')/ThumbnailContent";

$meta = api_get($metaurl, $authval, $apikeyval);

if (($meta['info']['http_code'] ?? 0) != 200) {
    bail(intval($meta['info']['http_code'] ?? 502), 'Upstream returned non-200');
}

$decoded = json_decode($meta['body'], true);

if (empty($decoded['value'][0]['ThumbnailUrl'])) {
    bail(404, 'No thumbnail available for this presentation');
}

$imgurl  = $decoded['value'][0]['ThumbnailUrl'];
$imgmime = $decoded['value'][0]['ContentMimeType'] ?? 'image/jpeg';

// Step 2: reject anything that isn't a raster image (SVG can carry scripts).
$safetypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($imgmime, $safetypes, true)) {
    bail(415, 'Content type not permitted');
}

// Step 3: fetch the actual image bytes through an authenticated request.
$imgtransport = new curl();
$imgtransport->setHeader([
    'Authorization: ' . $authval,
    'sfapikey: ' . $apikeyval,
]);
$imgbytes = $imgtransport->get($imgurl);

if ($imgtransport->get_errno()) {
    bail(502, 'Image fetch failed');
}

$imginfo = $imgtransport->get_info();
if (($imginfo['http_code'] ?? 0) != 200) {
    bail(intval($imginfo['http_code'] ?? 502), 'Image upstream error');
}

// Step 4: relay the image with appropriate caching headers.
header('Content-Type: ' . $imgmime);
header('Content-Length: ' . strlen($imgbytes));
header('Cache-Control: private, max-age=3600');
echo $imgbytes;
