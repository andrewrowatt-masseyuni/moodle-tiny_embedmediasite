<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Validates the declared return structure of the get_presentations service.
 *
 * @package    tiny_embedmediasite
 */

namespace tiny_embedmediasite\external;

/**
 * Checks that execute_returns() advertises the expected field names and types.
 *
 * @covers \tiny_embedmediasite\external\get_presentations
 */
final class get_presentations_test extends \advanced_testcase {

    /**
     * The thumbnail field inside list items must be an optional URL.
     */
    public function test_list_item_declares_optional_thumbnail(): void {
        $schema = get_presentations::execute_returns();
        $inner  = $schema->keys['list']->content;

        $this->assertArrayHasKey('thumbnail', $inner->keys,
            'The list item schema must include a thumbnail field');

        $field = $inner->keys['thumbnail'];
        $this->assertSame(VALUE_OPTIONAL, $field->required);
        $this->assertSame(PARAM_URL, $field->type);
    }

    /**
     * The top-level manage field must be an optional URL.
     */
    public function test_envelope_declares_optional_manage(): void {
        $schema = get_presentations::execute_returns();

        $this->assertArrayHasKey('manage', $schema->keys,
            'The envelope schema must include a manage field');

        $field = $schema->keys['manage'];
        $this->assertSame(VALUE_OPTIONAL, $field->required);
        $this->assertSame(PARAM_URL, $field->type);
    }

    /**
     * The page input parameter must be declared as PARAM_INT.
     */
    public function test_input_declares_page_param(): void {
        $params = get_presentations::execute_parameters();
        $this->assertArrayHasKey('page', $params->keys);
        $this->assertSame(PARAM_INT, $params->keys['page']->type);
    }
}
