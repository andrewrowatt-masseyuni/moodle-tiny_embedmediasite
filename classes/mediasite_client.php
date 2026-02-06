<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Mediasite API client — talks to the Presentations endpoint and
 * reshapes the wire format for the TinyMCE embed dialogue.
 *
 * @package    tiny_embedmediasite
 */

namespace tiny_embedmediasite;

defined('MOODLE_INTERNAL') || die();

class mediasite_client {

    /** @var int Window of results per call. */
    private $chunk;

    /** @var array Holds root/key/auth/portal values. */
    private $cfg;

    private function __construct(array $cfg) {
        $this->chunk = 10;
        $this->cfg   = $cfg;
    }

    public static function create(): self {
        $g = fn(string $n) => get_config('tiny_embedmediasite', $n);
        $p = $g('manageurl');
        return new self([
            'root'   => rtrim($g('basemediasiteurl'), '/'),
            'key'    => $g('sfapikey'),
            'auth'   => $g('authorization'),
            'portal' => ($p !== false && $p !== '') ? $p : null,
        ]);
    }

    public function load_page(int $idx): array {
        global $USER;

        $wire = $this->do_query($USER->username, $idx);
        $data = json_decode($wire, true);

        if (!is_array($data) || !isset($data['value'])) {
            throw new \moodle_exception('mediasiteapierror', 'tiny_embedmediasite');
        }

        $n = array_key_exists('odata.count', $data) ? intval($data['odata.count']) : 0;

        $envelope = [
            'list'      => array_map([$this, 'reshape'], $data['value']),
            'nologin'   => true,
            'norefresh' => true,
            'nosearch'  => true,
            'page'      => $idx,
            'pages'     => ($n > 0) ? intval(ceil($n / $this->chunk)) : 0,
        ];

        if ($this->cfg['portal'] !== null) {
            $envelope['manage'] = $this->cfg['portal'];
        }

        return $envelope;
    }

    private function do_query(string $who, int $idx): string {
        $off = $idx * $this->chunk;
        $qs  = http_build_query([
            '$select'  => 'full',
            '$orderby' => 'CreationDate desc',
            '$top'     => $this->chunk,
            '$skip'    => $off,
            '$filter'  => "Creator eq '{$who}'",
        ], '', '&');

        $h = new \curl();
        $h->setHeader($this->auth_headers());
        $r = $h->get($this->cfg['root'] . '/Api/v1/Presentations?' . $qs);

        if ($h->get_errno()) {
            throw new \moodle_exception('mediasiteapierror', 'tiny_embedmediasite');
        }
        return $r;
    }

    private function auth_headers(): array {
        return [
            'Content-Type: application/json',
            'Authorization: ' . $this->cfg['auth'],
            'Accept: application/json',
            'sfapikey: ' . $this->cfg['key'],
        ];
    }

    private function reshape(array $v): array {
        global $CFG, $OUTPUT;

        $when = self::parse_odata_ts($v['CreationDate'] ?? '');
        $ms   = isset($v['Duration']) ? intval($v['Duration']) : 0;
        $pid  = $v['Id'] ?? '';

        $out = [
            'title'              => $v['Title'] ?? '',
            'source'             => $v['PlayerUrl'] ?? '',
            'date'               => $when,
            'date_formatted'     => userdate($when),
            'author'             => $v['Owner'] ?? '',
            'icon'               => $OUTPUT->image_url(file_mimetype_icon('video/mp4'))->out(false),
            'thumbnail_width'    => 400,
            'thumbnail_height'   => 400,
            'mimetype'           => 'Video',
            'duration'           => $ms,
            'duration_formatted' => self::ms_to_text($ms),
        ];

        if ($pid !== '') {
            $out['thumbnail'] = $CFG->wwwroot
                . '/lib/editor/tiny/plugins/embedmediasite/thumbnail.php?id='
                . urlencode($pid);
        }

        return $out;
    }

    private static function parse_odata_ts(string $s): int {
        return preg_match('#Date\((\d+)\)#', $s, $m) ? intval($m[1] / 1000) : 0;
    }

    /**
     * Turn milliseconds into readable text via a tier-table walk.
     *
     * @param int $ms Non-negative millisecond count.
     * @return string Readable phrase or empty string.
     */
    public static function ms_to_text(int $ms): string {
        if ($ms <= 0) {
            return '';
        }
        $sec = intdiv($ms, 1000);
        $map = [
            3600 => ['duration_hour',   'duration_hours'],
            60   => ['duration_minute', 'duration_minutes'],
            1    => ['duration_second', 'duration_seconds'],
        ];
        $acc = [];
        foreach ($map as $unit => $keys) {
            $qty = intdiv($sec, $unit);
            $sec -= $qty * $unit;
            if ($qty >= 1) {
                $acc[] = $qty . ' ' . get_string($keys[($qty > 1) ? 1 : 0], 'tiny_embedmediasite');
            }
        }
        return implode(' ', $acc);
    }
}
