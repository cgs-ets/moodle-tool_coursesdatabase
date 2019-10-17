<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test coursesdatabase settings.
 *
 * @package   tool_coursesdatabase
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_coursesdatabase_test');


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testsettings', 'tool_coursesdatabase'));

$returnurl = new moodle_url('/admin/settings.php', array('section' => 'tool_coursesdatabase_settings'));
$coursesdatabase = new tool_coursesdatabase_sync();
$coursesdatabase->test_settings();

echo $OUTPUT->continue_button($returnurl);
echo $OUTPUT->footer();

