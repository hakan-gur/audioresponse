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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace qtype_audioresponse\privacy;

use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy metadata provider for the Audio Response question type.
 */
class provider implements \core_privacy\local\metadata\provider {

    /**
     * Describe the Moodle subsystems used to store audio responses.
     *
     * The question engine owns attempt data and Moodle's file subsystem owns
     * the recording files; this question type does not keep a separate copy.
     *
     * @param collection $collection The initialised metadata collection.
     * @return collection The updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_subsystem_link(
            'core_question',
            [],
            'privacy:metadata:core_question'
        );
        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:core_files'
        );

        return $collection;
    }
}
