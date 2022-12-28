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

namespace mod_journaldeclasse;

use DateTimeImmutable;
use DateInterval;
use core_date;

/**
 * The entry_set class.
 * From an activity and a date generate the related entries: the current date, the
 * day before and the next 10 events.
 *
 * @package     mod_journaldeclasse
 * @copyright   2022 Manuel Tondeur <manu@numethic.education>, Jacques Theys <jacques@numethic.education>
 * @license      https://www.gnu.org/licenses/agpl-3.0.html GNU AGPL v3 or later
 */

class entry_set {
    public $current_day;
    public $past_day;
    public $future_events;

    function __construct($current_date, $activity_id) {
        $current_time = new DateTimeImmutable($current_date, core_date::get_user_timezone_object());
        $last_day_start = $current_time->setTime(0, 0, 0);
        $last_day_end = $current_time->setTime(23, 59, 59);
        $this->current_day = $this->get_entries($activity_id, $last_day_start->getTimestamp(), $last_day_end->getTimestamp());

        $past_day_start = $last_day_start->sub(new DateInterval('P1D'));
        $past_day_end = $last_day_end->sub(new DateInterval('P1D'));
        $this->past_day = $this->get_entries($activity_id, $past_day_start->getTimestamp(), $past_day_end->getTimestamp());

        $this->future_events = $this->get_future_events($activity_id, $current_time->getTimestamp());
    }

    private function get_entries($activity_id, $start, $end) {
        global $DB;
        $entries = $DB->get_records_select(
            'journaldeclasse_entry',
            "entry_journaldeclasse = ". $activity_id ." AND date_entry >= ". $start ." AND date_entry <= ". $end
        );

        $entries = $this->detail_periods($entries);
        
        $entries = array_values(array_map(array($this, 'stdclass_to_array'), $entries));

        return $entries;
    }

    private function get_future_events($activity_id, $datetime_after) {
        global $DB;

        $entries = $DB->get_records_select(
            'journaldeclasse_entry',
            "entry_journaldeclasse = ". $activity_id ." AND date_entry > ". $datetime_after ." AND moodle_event IS NOT NULL",
            null,
            'date_entry',
            '*',
            0,
            10
        );

        $entries = $this->detail_periods($entries);

        $entries = array_values(array_map(array($this, 'stdclass_to_array'), $entries));

        return $entries;
    }

    private function detail_periods($entries) {
        global $DB;

        foreach ($entries as $entry) {
            $entry->date_entry = (
                new DateTimeImmutable('@' .$entry->date_entry, core_date::get_user_timezone_object())
            )->format("Y-m-d");
            $entry->period = (array) $DB->get_record_sql(
                "SELECT MIN(p.period_start) AS start, MAX(p.period_end) AS end
                   FROM {journaldeclasse_period} p, {journaldeclasse_entry_period} ep
                  WHERE ep.entry_id = ". $entry->id ." AND ep.period_id = p.id;"
            );
        }
        return $entries;
    }

    private function stdclass_to_array($obj) {
        return (array) $obj;
    }
}
