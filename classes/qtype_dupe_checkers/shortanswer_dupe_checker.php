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
 * shortanswer_dupe_checker class.
 *
 * Will check if shortanswer questions are duplicate
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\qtype_dupe_checkers;

defined('MOODLE_INTERNAL') || die();

class shortanswer_dupe_checker implements qtype_dupe_checker {

    public static function questions_are_duplicate($questiona, $questionb) {
        if ($questiona->options->usecase !== $questionb->options->usecase) {
            return false;
        }

        // Need to reorder array keys because they are id indexed.
        $answersa = array_values($questiona->options->answers);
        $answersb = array_values($questionb->options->answers);

        // TODO: Do this better, for now we assume they have the same order.
        foreach ($answersa as $key => $answer) {
            if (!self::answers_are_duplicate($answer, $answersb[$key])) {
                return false;
            }
        }

        return true;
    }

    private static function answers_are_duplicate($answera, $answerb) {
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