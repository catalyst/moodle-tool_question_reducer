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

namespace tool_question_reducer\tests;

defined('MOODLE_INTERNAL') || die();

class qcat_dupe_question_merger_test extends \advanced_testcase {

    protected function setUp() {
    }

    public function test_merges_duplicate_questions_within_qcat() {

        // For now we just create two duplicate short answer questions here.
        // In future we want to rely on some dupe question generators for each supported question type.
        $this->assertTrue(true);
    }
}

