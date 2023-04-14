<?php
// This file is part of mod_journaldeclasse - https://numethic.education/
//
// mod_journaldeclasse is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with mod_journaldeclasse.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_journaldeclasse\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

/**
 * The entry form class.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
 * @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

 class entry extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'coursemoduleid');
        $mform->setType('coursemoduleid', PARAM_INT);
        $mform->setDefault('coursemoduleid', $this->_customdata['coursemoduleid']);

        $mform->addElement('text', 'title', get_string('entryname', 'mod_journaldeclasse'));
        $mform->setType('title', PARAM_NOTAGS);

        $mform->addElement('date_selector', 'date_entry', get_string('entrydate', 'mod_journaldeclasse'));

        $select = $mform->addElement('select', 'periods', get_string('periods', 'mod_journaldeclasse'));
        $select->setMultiple(true);
        $this->add_periods_options();

        $mform->addElement('advcheckbox', 'hasevent', get_string('linkedtoevent', 'mod_journaldeclasse'), get_string('yes'));
        $mform->setType('hasevent', PARAM_RAW);

        $mform->addElement('editor', 'description', get_string('entrydescription', 'mod_journaldeclasse'));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'moodle_event', '-1');
        $mform->setType('moodle_event', PARAM_INT);

        $this->add_action_buttons(false);
    }

    private function add_periods_options() {
        global $DB;

        $periodform = $this->_form->getElement('periods');
        $periodschemaid = $this->_customdata['periodschemaid'];
        $periods = $DB->get_records('journaldeclasse_period', array('period_schema' => $periodschemaid));

        foreach ($periods as $period) {
            $periodform->addOption(
                $period->period_start .' – ' . $period->period_end,
                $period->id
            );
        }
    }

    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            $data->description_format = $data->description['format'];
            $data->description = $data->description['text'];

            $data->entry_journaldeclasse = $data->coursemoduleid;

            if (!$data->hasevent) {
                $data->moodle_event = null;
            }
        }
        return $data;
    }

    public function set_data($data) {
        if (!$data) {
            return;
        }

        global $DB;

        // Check if it is an edit.
        if (property_exists($data, 'id')) {
            $data->description = array(
                'text' => $data->description,
                'format' => $data->description_format,
            );

            $id = intval($data->id);

            if ($id > 0) {
                $selectedperiods = $DB->get_records_sql(
                    "SELECT p.id
                    FROM {journaldeclasse_period} as p, {journaldeclasse_entry_period} as ep
                    WHERE ep.entry_id = ? AND ep.period_id = p.id;",
                    [
                        $id,
                    ]
                );

                $data->periods = [];
                foreach ($selectedperiods as $period) {
                    array_push($data->periods, $period->id);
                }

            }
        }
        

        parent::set_data($data);
    }

    function validation($data, $files) {
        $errors= array();

        return $errors;
    }
 }
