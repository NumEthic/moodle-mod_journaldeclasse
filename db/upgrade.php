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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_journaldeclasse
 * @category    upgrade
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
* @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute mod_journaldeclasse upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_journaldeclasse_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022111803) {

        // Define table journaldeclasse_entry to be created.
        $table = new xmldb_table('journaldeclasse_entry');

        // Adding fields to table journaldeclasse_entry.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('date_entry', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('moodle_event', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table journaldeclasse_entry.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for journaldeclasse_entry.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table journaldeclasse_periodschema to be created.
        $table = new xmldb_table('journaldeclasse_periodschema');

        // Adding fields to table journaldeclasse_periodschema.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table journaldeclasse_periodschema.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for journaldeclasse_periodschema.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table journaldeclasse_period to be created.
        $table = new xmldb_table('journaldeclasse_period');

        // Adding fields to table journaldeclasse_period.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('period_start', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('period_end', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('day_of_week', XMLDB_TYPE_CHAR, '10', null, null, null, '1-5');
        $table->add_field('period_schema', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table journaldeclasse_period.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('period_schema-id', XMLDB_KEY_FOREIGN, ['period_schema'], 'journaldeclasse_periodschema', ['id']);

        // Conditionally launch create table for journaldeclasse_period.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table journaldeclasse_entry_period to be created.
        $table = new xmldb_table('journaldeclasse_entry_period');

        // Adding fields to table journaldeclasse_entry_period.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('entry_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('period_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table journaldeclasse_entry_period.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('entry-id', XMLDB_KEY_FOREIGN, ['entry_id'], 'journaldeclasse_entry', ['id']);
        $table->add_key('period-id', XMLDB_KEY_FOREIGN, ['period_id'], 'journaldeclasse_period', ['id']);

        // Conditionally launch create table for journaldeclasse_entry_period.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Journaldeclasse savepoint reached.
        upgrade_mod_savepoint(true, 2022111803, 'journaldeclasse');
    }

    if ($oldversion < 2022121203) {
        // Define field id to be added to journaldeclasse_entry.
        $table = new xmldb_table('journaldeclasse_entry');
        $field = new xmldb_field('entry_journaldeclasse', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'moodle_event');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Journaldeclasse savepoint reached.
        upgrade_mod_savepoint(true, 2022121203, 'journaldeclasse');
    }

    if ($oldversion < 2022122901) {

        // Define field description_format to be added to journaldeclasse_entry.
        $table = new xmldb_table('journaldeclasse_entry');
        $field = new xmldb_field('description_format', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '1', 'entry_journaldeclasse');

        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Journaldeclasse savepoint reached.
        upgrade_mod_savepoint(true, 2022122901, 'journaldeclasse');
    }

    if ($oldversion < 2023010201) {

        // Define field journaldeclasse_schema to be added to journaldeclasse.
        $table = new xmldb_table('journaldeclasse');
        $field = new xmldb_field('journaldeclasse_schema', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'introformat');

        // Conditionally launch add field journaldeclasse_schema.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('journaldeclasse_schema-id', XMLDB_KEY_FOREIGN, ['journaldeclasse_schema'], 'journaldeclasse_periodschema', ['id']);

        // Launch add key journaldeclasse_schema-id.
        $dbman->add_key($table, $key);

        // Journaldeclasse savepoint reached.
        upgrade_mod_savepoint(true, 2023010201, 'journaldeclasse');
    }


    return true;
}
