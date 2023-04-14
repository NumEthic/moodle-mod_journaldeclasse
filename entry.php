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

// Is it a deletion ?
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete > 0 && $id > 0) {
    $date = required_param('date', PARAM_NOTAGS);
    $DB->delete_records(
        'journaldeclasse_entry_period',
        [
            "entry_id" => $id,
        ]
    );
    $DB->delete_records(
        'journaldeclasse_entry',
        [
            "id" => $id,
        ]
    );
    //TODO Remove related events

    $journalurl = new moodle_url('/mod/journaldeclasse/view.php', array('id'=> $coursemoduleid, 'day'=> $date));
    redirect($journalurl, get_string('entrydeleted', 'mod_journaldeclasse'));
}

$cm = get_coursemodule_from_id('journaldeclasse', $coursemoduleid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
require_login($course, true, $cm);

if ($id > 0) {
    $entry = $DB->get_record('journaldeclasse_entry', array('id' => $id));
    $PAGE->set_title(get_string('editentry', 'mod_journaldeclasse'));
} else {
    $entry = (object) array();
    $PAGE->set_title(get_string('createentry', 'mod_journaldeclasse'));
}

$entry->coursemoduleid = $coursemoduleid;

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url(
    '/mod/journaldeclasse/entry.php',
);
$PAGE->set_context($modulecontext);

$periodschemaid = $DB->get_record('journaldeclasse', array('id' => $cm->id), 'journaldeclasse_schema')->journaldeclasse_schema;
$mform = new \mod_journaldeclasse\form\entry(null, array('periodschemaid' => $periodschemaid, 'coursemoduleid' => $coursemoduleid));

if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    // Data has been submitted.
    $submiteddata = $mform->get_data();

    if ($id > 0) {
        // Update entry.
        $DB->update_record(
            'journaldeclasse_entry',
            $submiteddata
        );
        $DB->delete_records(
            'journaldeclasse_entry_period',
            ['entry_id' => $id]
        );
    } else {
        // Create entry.
        $id = $DB->insert_record(
            'journaldeclasse_entry',
            $submiteddata,
            true,
        );

        if ($submiteddata->hasevent) {
            // Create event.
        }
    }

    // Create entry-period relationship.
    foreach ($submiteddata->periods as $period) {
        $DB->insert_record(
            'journaldeclasse_entry_period',
            ['entry_id' => $id, 'period_id' => $period]
        );
    }

    // Redirect to journaldeclasse overview at the date of the new entry.
    $day = usergetdate($submiteddata->date_entry);
    $day = $day["year"].'-'.sprintf('%02d', $day["mon"]).'-'.sprintf('%02d', $day["mday"]);
    $journalurl = new moodle_url('/mod/journaldeclasse/view.php', array('id'=> $submiteddata->coursemoduleid, 'day'=> $day));
    redirect($journalurl, get_string('entrysaved', 'mod_journaldeclasse'));
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    $mform->set_data($entry);

    // Display the form.
    $body = $mform->render();
}

echo $OUTPUT->header();

echo $body;

echo $OUTPUT->footer();
