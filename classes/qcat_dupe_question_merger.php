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
 * qcat_dupe_question_merger class.
 *
 * Will merge dupelicate questions within supplied question category.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

class qcat_dupe_question_merger {

    public static $supportedquestiontypes = array(
        'shortanswer'
    );

    public static function merge_duplicates($qcat) {
        foreach (self::$supportedquestiontypes as $qtype) {
            self::merge_qtype_duplicates($qcat, $qtype);
        }
    }

    public static function merge_qtype_duplicates($qcat, $qtype) {
        // Get all questions with same name
        $samenamegroups = self::get_question_groups_with_same_name($qcat, $qtype);

        // Group now based on all question data.
        $identicalquestions = array();
        foreach ($samenamegroups as $group) {
            $identicalquestions = $identicalquestions + self::subgroup_based_on_question($group, $qtype);
        }

        self::print_counts($identicalquestions);
    }

    private static function get_question_groups_with_same_name($qcat, $qtype) {
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
            'qtype'         => $qtype,
            'qtypetwo'      => $qtype,
        );

        $questions = $DB->get_records_sql($sql, $params);

        // Attach question type data
        get_question_options($questions);

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

    private static function subgroup_based_on_question($questions, $qtype) {
        $subgroups = array();

        foreach ($questions as $question) {
            $questioningroup = false;
            foreach ($subgroups as $key => $group) {
                $groupquestion = reset($group);

                // Only need to check against first.
                if (self::questions_are_same($question, $groupquestion, $qtype)) {
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
                unset($subgroups[$key]);
            }
        }

        return $subgroups;
    }

    private static function questions_are_same($questiona, $questionb, $qtype) {
        $basecomparisonattributes = array(
            'questiontext',
            'questiontextformat',
            'generalfeedback',
            'generalfeedbackformat',
            'defaultmark',
            'penalty',
            'length',
            'hidden'
        );

        foreach ($basecomparisonattributes as $attribute) {
            if ($questiona->$attribute !== $questionb->$attribute) {
                return false;
            }
        }

        // TODO: Need to check 'hints'

        $questiontypedupechecker = "\\tool_question_reducer\\qtype_dupe_checkers\\{$qtype}_dupe_checker";
        if (!$questiontypedupechecker::questions_are_duplicate($questiona, $questionb)) {
            return false;
        }

        return true;
    }

    private static function print_counts($questiongroups) {
        $totalcount = 0;

        foreach ($questiongroups as $questiongroup) {
            $totalcount += count($questiongroup);
        }

        $finalcount = count($questiongroups);

        echo "Can reduce {$totalcount} questions to {$finalcount} questions\n";
    }


}
