<?php
// This file is part of mod_journaldeclasse - https://numethic.education/
//
// mod_journaldeclasse is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// mod_journaldeclasse is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with mod_journaldeclasse.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Edit/Create an entry of mod_journaldeclasse.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Journaldeclasse entry module id.
$id = optional_param('id', 0, PARAM_INT);
// Course module id.
$coursemoduleid = required_param('coursemoduleid', PARAM_INT);

echo '<br><br><br>';
echo var_dump($coursemoduleid);

$cm = get_coursemodule_from_id('journaldeclasse', 2, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

if ($id) {
    $entry = $DB->get_record('journaldeclasse_entry', array('id' => $id));
} else {
    $entry = array();
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url(
    '/mod/journaldeclasse/entry.php',
    //array('id' => $entry->id)
);
// $PAGE->set_title(format_string($moduleinstance->name));
// $PAGE->set_heading(format_string($course->fullname));
// $PAGE->set_context($modulecontext);

echo $OUTPUT->header();

echo $body;

echo $OUTPUT->footer();
