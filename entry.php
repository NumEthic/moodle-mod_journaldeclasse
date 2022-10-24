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

// Entry module id.
$id = optional_param('id', 0, PARAM_INT);
// $j = optional_param('j', 0, PARAM_INT);

if ($id) {
    // $entry = get_coursemodule_from_id('journaldeclasse__entry', $id, 0, false, MUST_EXIST);
    // $cm = get_coursemodule_from_id('journaldeclasse', $entry->cm, 0, false, MUST_EXIST);
    // $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    // $moduleinstance = $DB->get_record('journaldeclasse', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    // $moduleinstance = $DB->get_record('journaldeclasse', array('id' => $j), '*', MUST_EXIST);
    // $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    // $cm = get_coursemodule_from_instance('journaldeclasse', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

// require_login($course, true, $cm);

// $modulecontext = context_module::instance($cm->id);


$PAGE->set_url(
    '/mod/journaldeclasse/entry.php',
    //array('id' => $entry->id)
);
// $PAGE->set_title(format_string($moduleinstance->name));
// $PAGE->set_heading(format_string($course->fullname));
// $PAGE->set_context($modulecontext);

$body = $OUTPUT->render_from_template("journaldeclasse/entry");

echo $OUTPUT->header();

echo $body;

echo $OUTPUT->footer();
