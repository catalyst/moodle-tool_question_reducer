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
 * context_question_reducer class.
 *
 * Will reduce questions within supplied context.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\qcat_dupe_question_merger;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/admin/tool/objectfs/lib.php');

class shortanswer_merger {

    public static $qtype = 'shortanswer';

    public static function merge_duplicates($qcat) {
        // Get all shortanswer questions with same name
        $samenamegroups = self::get_question_groups_with_same_name($qcat);

        // Group now based on base question data.
        $samequestiongroups = array();
        foreach ($samenamegroups as $group) {
            $samequestiongroups[] = self::subgroup_based_on_question($group);
        }

        $identicalquestions[] = array();
        foreach ($samequestiongroups as $group) {
            $identicalquestions[] = self::subgroup_based_on_specific_qtype_details($group);
        }

        var_dump($identicalquestions);
    }

    private static function get_question_groups_with_same_name($qcat) {
        global $DB;
        $sql = "SELECT * from {question} q
                WHERE q.category = :qcatid
                    AND q.qtype = :qtype
                    AND q.name in ( SELECT q.name
                                    FROM {question} q
                                    INNER JOIN mdl_question_categories qc on qc.id = q.category
                                    WHERE q.category = :qcatidtwo
                                        AND q.qtype = :qtypetwo
                                    GROUP BY q.name
                                    HAVING count(q.name) > 1)";

        $params = array(
            'qcatid'        => $qcat->id,
            'qcatidtwo'     => $qcat->id,
            'qtype'         => self::$qtype,
            'qtypetwo'      => self::$qtype,
        );

        $questions = $DB->get_records_sql($sql, $params);

        // Group by name
        $groupedquestions = array();
        foreach ($questions as $question) {
            if (!isset($groupedquestions[$question->name])) {
                $groupedquestions[$question->name] = array();
            }
            $groupedquestions[$question->name][] = $question;
        }

        return $groupedquestions;
    }

    private static function subgroup_based_on_question($questions) {
        $subgroups = array();

        foreach ($questions as $question) {
            $questioningroup = false;
            foreach ($subgroups as $key => $group) {
                $groupquestion = reset($group);

                // Only need to check against first.
                if (self::questions_are_same($question, $groupquestion)) {
                    $subgroups[$key][] = $question;
                    $questioningroup = true;
                    break;
                }
            }

            if (!$questioningroup) {
                $subgroups[] = array($question);
            }
        }

        foreach ($subgroups as $key => $subgroup) {
            if (count($subgroup) < 2) {
                unset($subgroup[$key]);
            }
        }

        return $subgroups;
    }

    private static function questions_are_same($qa, $qb) {
        $comparisonattributes = array(
            'questiontext',
            'questiontextformat',
            'generalfeedback',
            'generalfeedbackformat',
            'defaultmark',
            'penalty',
            'length',
            'hidden'
        );

        foreach ($comparisonattributes as $attribute) {
            if ($qa->$attribute !== $qb->$attribute) {
                return false;
            }
        }

        return true;
    }

    private static function subgroup_based_on_specific_qtype_details($questions) {
        return $questions;
    }
}