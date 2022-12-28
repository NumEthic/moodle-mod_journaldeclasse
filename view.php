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
 * Prints an instance of mod_journaldeclasse.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$j = optional_param('j', 0, PARAM_INT);

// Last date to show.
$day = optional_param(
    'day',
    (new DateTime('now', core_date::get_user_timezone_object()))->format('Y-m-d'),
    PARAM_NOTAGS
);

if ($id) {
    $cm = get_coursemodule_from_id('journaldeclasse', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('journaldeclasse', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('journaldeclasse', array('id' => $j), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('journaldeclasse', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = mod_journaldeclasse\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('journaldeclasse', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/journaldeclasse/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$editmode = $PAGE->user_is_editing();

$day_before = (new DateTime($day))->sub(new DateInterval('P1D'))->format('Y-m-d');
$entry_set = new \mod_journaldeclasse\entry_set($day, $id);

$data = [
    'editmode' => $editmode,
    'coursemoduleid' => $id,
    'lastday' => [
        'day' => $day,
        'entries' => $entry_set->current_day,
    ],
    'daybefore' => [
        'day' => $day_before,
        'entries' => $entry_set->past_day,
    ],
    'futureevents' => $entry_set->future_events,
    'searchcontext' => [
        'searchstring' => 'Rechercher une entrée…',
        'hiddenfields' => [
            ['name' => 'id', 'value' => $id],
            ['name' => 'day', 'value' => $day],
        ]
    ]
];

$body = $OUTPUT->render_from_template("journaldeclasse/journal", $data);

echo $OUTPUT->header();

echo $body;

echo $OUTPUT->footer();
