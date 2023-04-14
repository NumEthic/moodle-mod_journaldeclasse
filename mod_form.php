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

/**
 * The main mod_journaldeclasse configuration form.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */
class mod_journaldeclasse_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('journaldeclassename', 'mod_journaldeclasse'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'journaldeclassename', 'mod_journaldeclasse');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Add period schema field.
        $mform->addElement('select', 'journaldeclasse_schema', get_string('periodschema', 'mod_journaldeclasse'));
        $this->set_period_schema_options();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Add options to the period_schema select form.
     */
    private function set_period_schema_options() {
        global $DB;

        $periodschemas = $this->_form->getElement('journaldeclasse_schema');
        $hasschema = false;
        foreach ($DB->get_records('journaldeclasse_periodschema') as $ps) {
            $hasschema = true;
            $periodschemas->addOption(
                $ps->name,
                $ps->id
            );
        }

        // If there is no schema, create an empty one.
        if (!$hasschema) {
            $newschemaid = $DB->insert_record(
                'journaldeclasse_periodschema',
                array('name' => 'default')
            );
            $periodschemas->addOption(
                'default',
                $newschemaid
            );
        }
    }
}
