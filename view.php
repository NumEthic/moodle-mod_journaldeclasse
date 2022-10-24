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

$event = \mod_journaldeclasse\event\course_module_viewed::create(array(
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

$data = [
    'editmode' => $editmode,
    'coursemoduleid' => $id,
    'lastday' => [
        'day' => '2022-10-24',
        'entries' => [
            0 => [
                'id' => 1,
                'date' => '2022-10-24',
                'title' => 'Théorème des résidus',
                'description' => 'Repellendus omnis et beatae nostrum sit. Ipsum tempore a qui necessitatibus. Maxime atque error atque est fugiat laborum eum. Placeat maxime doloribus inventore veritatis neque. Quae occaecati sit sed itaque eos dolorem assumenda. Tempora quia id nihil a',
                'period' => [
                    'start' => '8h30',
                    'end' => '10h00',
                ]
            ],
            1 => [
                'id' => 2,
                'date' => '2022-10-24',
                'title' => 'Examen compliqué',
                'description' => '',
                'period' => [
                    'start' => '11h30',
                    'end' => '12h00',
                ]
            ],
        ]
    ],
    'daybefore' => [
        'day' => '2022-23-10',
        'entries' => [
            0 => [
                'id' => 4,
                'date' => '2022-23-10',
                'title' => 'Lemme des résidus',
                'description' => 'Repellendus omnis et beatae nostrum sit. Ipsum tempore a qui necessitatibus. Maxime atque error atque est fugiat laborum eum. Placeat maxime doloribus inventore veritatis neque. Quae occaecati sit sed itaque eos dolorem assumenda. Tempora quia id nihil a',
                'period' => [
                    'start' => '8h30',
                    'end' => '10h00',
                ]
            ],
            1 => [
                'id' => 5,
                'date' => '2022-10-24',
                'title' => 'Examen simple',
                'description' => '',
                'period' => [
                    'start' => '8h30',
                    'end' => '10h00',
                ]
            ],
        ]
    ],
    'futureevents' => [
        0 => [
            'id' => 3,
            'date' => '2022-25-10',
            'title' => 'Devoir',
            'description' => 'Repellendus omnis et beatae nostrum sit. Ipsum tempore a qui necessitatibus. Maxime atque error atque est fugiat laborum eum. Placeat maxime doloribus inventore veritatis neque. Quae occaecati sit sed itaque eos dolorem assumenda. Tempora quia id nihil a',
            'period' => [
                'start' => '8h30',
                'end' => '10h00',
            ]
        ],
        1 => [
            'id' => 3,
            'date' => '2022-11-11',
            'title' => 'Examen simple et compliqué',
            'description' => '',
            'period' => [
                'start' => '8h30',
                'end' => '10h00',
            ]
        ],
    ]

];

$body = $OUTPUT->render_from_template("journaldeclasse/journal", $data);

echo $OUTPUT->header();

echo $body;

echo $OUTPUT->footer();
