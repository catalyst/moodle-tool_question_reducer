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
 * comparer class.
 *
 * Will help compare objects and arrays.
 *
 * @package   tool_question_reducer
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_question_reducer\helpers;

defined('MOODLE_INTERNAL') || die();

class comparer {
    public static function object_arrays_are_duplicate($arraya, $arrayb, $comparisonattributes) {
        $arraya = array_values($arraya);
        $arrayb = array_values($arrayb);

        if (count(array_keys($arraya)) !== count(array_keys($arrayb))) {
            return false;
        }

        // TODO: Do this better, for now we assume they have the same order.
        foreach ($arraya as $key => $object) {
            if (!self::objects_are_duplicate($object, $arrayb[$key], $comparisonattributes)) {
                return false;
            }
        }
        return true;
    }

    public static function objects_are_duplicate($objecta, $objectb, $comparisonattributes) {
        foreach ($comparisonattributes as $attribute) {
            if ($objecta->$attribute !== $objectb->$attribute) {
                return false;
            }
        }
        return true;
    }


}
