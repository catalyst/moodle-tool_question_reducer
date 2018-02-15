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

use tool_question_reducer\qcat_dupe_question_merger;
use tool_question_reducer\question_dupe_checker;

class qcat_dupe_question_merger_test extends \advanced_testcase {

    protected function setUp() {
        $this->resetAfterTest();
    }

    public function test_merges_duplicate_questions_within_qcat() {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $supportedquestiontypes = question_dupe_checker::get_supported_question_types();

        foreach ($supportedquestiontypes as $qtype) {
            $cat = $generator->create_question_category();
            $qa = $generator->create_question($qtype, null, array('category' => $cat->id));
            $qb = $generator->create_question($qtype, null, array('category' => $cat->id));
            $qc = $generator->create_question($qtype, null, array('category' => $cat->id));

            qcat_dupe_question_merger::merge_duplicates($cat);

            $questioncount = $DB->count_records('question', array('category' => $cat->id));
            $this->assertEquals(1, $questioncount);
        }
    }
}

