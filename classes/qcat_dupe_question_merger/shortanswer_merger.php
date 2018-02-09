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

require_once($CFG->libdir.'/questionlib.php');

class shortanswer_merger {

    public static $qtype = 'shortanswer';

    public static function merge_duplicates($qcat) {
        // Get all questions with same name
        $samenamegroups = self::get_question_groups_with_same_name($qcat);

        // Group now based on all question data.
        $identicalquestions = array();
        foreach ($samenamegroups as $group) {
            $identicalquestions = $identicalquestions + self::subgroup_based_on_question($group);
        }

        self::print_counts($identicalquestions);
    }

    private static function print_counts($questiongroups) {
        $totalcount = 0;

        foreach ($questiongroups as $questiongroup) {
            $totalcount += count($questiongroup);
        }

        $finalcount = count($questiongroups);

        echo "Can reduce {$totalcount} questions to {$finalcount} questions\n";
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
                unset($subgroups[$key]);
            }
        }

        return $subgroups;
    }

    private static function questions_are_same($questiona, $questionb) {
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

        if (!self::questions_are_same_for_type($questiona, $questionb)) {
            return false;
        }

        return true;
    }

    private static function questions_are_same_for_type($questiona, $questionb) {
        if ($questiona->options->usecase !== $questionb->options->usecase) {
            return false;
        }

        // Need to reorder array keys because they are id indexed.
        $answersa = array_values($questiona->options->answers);
        $answersb = array_values($questionb->options->answers);

        // TODO: Do this better, for now we assume they have the same order.
        foreach ($answersa as $key => $answer) {
            if (!self::answers_are_same($answer, $answersb[$key])) {
                return false;
            }
        }

        return true;
    }

    private static function answers_are_same($answera, $answerb) {
        $comparisonattributes = array(
            'answer',
            'answerformat',
            'fraction',
            'feedback',
            'feedbackformat'
        );

        foreach ($comparisonattributes as $attribute) {
            if ($answera->$attribute !== $answerb->$attribute) {
                return false;
            }
        }

        return true;
    }
}