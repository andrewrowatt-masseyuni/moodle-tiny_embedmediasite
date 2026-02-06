<?php
// This file is part of Moodle - http://moodle.org/
// SPDX-License-Identifier: GPL-3.0-or-later
// Copyright 2026, Andrew Rowatt <A.J.Rowatt@massey.ac.nz>.

/**
 * Unit tests for the duration formatting logic in mediasite_client.
 *
 * @package    tiny_embedmediasite
 */

namespace tiny_embedmediasite;

/**
 * Exercises {@see mediasite_client::ms_to_text} via reflection because
 * the method is the only piece of the class that can be tested without
 * an actual Mediasite server.
 *
 * @covers \tiny_embedmediasite\mediasite_client
 */
final class mediasite_client_test extends \advanced_testcase {

    /**
     * Invoke the public static ms_to_text method.
     *
     * @param int $input Milliseconds.
     * @return string Formatted result.
     */
    private function run_formatter(int $input): string {
        return mediasite_client::ms_to_text($input);
    }

    /**
     * Verify that zero and negative inputs yield an empty string.
     */
    public function test_non_positive_returns_blank(): void {
        $this->assertSame('', $this->run_formatter(0));
        $this->assertSame('', $this->run_formatter(-5000));
    }

    /**
     * Verify singular labels for exactly one of each unit.
     */
    public function test_singular_units(): void {
        $this->assertSame('1 Hour', $this->run_formatter(3600000));
        $this->assertSame('1 Minute', $this->run_formatter(60000));
        $this->assertSame('1 Second', $this->run_formatter(1000));
    }

    /**
     * Verify plural labels.
     */
    public function test_plural_units(): void {
        $this->assertSame('2 Hours', $this->run_formatter(7200000));
        $this->assertSame('5 Minutes', $this->run_formatter(300000));
        $this->assertSame('30 Seconds', $this->run_formatter(30933));
    }

    /**
     * Verify mixed combinations.
     */
    public function test_combined(): void {
        $this->assertSame('4 Minutes 35 Seconds', $this->run_formatter(275000));
        $this->assertSame('1 Hour 8 Minutes 55 Seconds', $this->run_formatter(4135000));
        $this->assertSame('2 Hours 30 Minutes 45 Seconds', $this->run_formatter(9045000));
    }
}
