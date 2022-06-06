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
 * Library of interface functions and constants.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function journaldeclasse_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_journaldeclasse into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_journaldeclasse_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function journaldeclasse_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('journaldeclasse', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_journaldeclasse in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_journaldeclasse_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function journaldeclasse_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('journaldeclasse', $moduleinstance);
}

/**
 * Removes an instance of the mod_journaldeclasse from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function journaldeclasse_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('journaldeclasse', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('journaldeclasse', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of mod_journaldeclasse?
 *
 * This function returns if a scale is being used by one mod_journaldeclasse
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_journaldeclasse instance.
 */
function journaldeclasse_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('journaldeclasse', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_journaldeclasse.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_journaldeclasse instance.
 */
function journaldeclasse_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('journaldeclasse', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_journaldeclasse instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function journaldeclasse_grade_item_update($moduleinstance, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/journaldeclasse', $moduleinstance->course, 'mod', 'mod_journaldeclasse', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_journaldeclasse instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function journaldeclasse_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/journaldeclasse', $moduleinstance->course, 'mod', 'journaldeclasse',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_journaldeclasse grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function journaldeclasse_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/journaldeclasse', $moduleinstance->course, 'mod', 'mod_journaldeclasse', $moduleinstance->id, 0, $grades);
}

/**
 * Extends the global navigation tree by adding mod_journaldeclasse nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $journaldeclassenode An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function journaldeclasse_extend_navigation($journaldeclassenode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_journaldeclasse settings.
 *
 * This function is called when the context for the page is a mod_journaldeclasse module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $journaldeclassenode {@see navigation_node}
 */
function journaldeclasse_extend_settings_navigation($settingsnav, $journaldeclassenode = null) {
}
