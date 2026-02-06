<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Declares the AJAX-callable web service for this plugin.
 *
 * @package    tiny_embedmediasite
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tiny_embedmediasite_get_presentations' => [
        'classname'   => \tiny_embedmediasite\external\get_presentations::class,
        'description' => 'Retrieve a page of the current user\'s Mediasite presentations',
        'type'        => 'read',
        'ajax'        => true,
    ],
];

$services = [];
