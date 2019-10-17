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
 * Strings for component 'tool_coursesdatabase', language 'en'.
 *
 * @package   tool_coursesdatabase
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type database server IP address or host name. Use a system DSN name if using ODBC.';
$string['dbname'] = 'Database name';
$string['dbname_desc'] = 'Leave empty if using a DSN name in database host.';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special database setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other databases. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbuser'] = 'Database user';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Debug ADOdb connection to external database - use when getting empty page during login. Not suitable for production sites!';
$string['pluginname'] = 'Courses external database';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your course creation.';
$string['settings'] = 'Settings';
$string['settings_desc'] = 'You can use an external database (of nearly any kind) to control your courses.';
$string['testsettings'] = "Test settings";
$string['localcoursefield'] = 'Local course field';
$string['localcategoryfield'] = 'Local category field';
$string['remotecoursetable'] = 'Remote courses table';
$string['remotecoursetable_desc'] = 'Specify the name of the table that contains list of courses.';
$string['remotecoursefield'] = 'Remote course field';
$string['remotecoursefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the course table.';
$string['coursefullname'] = 'Remote course fullname field';
$string['courseshortname'] = 'Remote course shortname field';
$string['courseidnumber'] = 'Remote course idnumber field';
$string['coursecategory'] = 'Remote course category field';
$string['defaultcategory'] = 'Default new course category';
$string['defaultcategory_desc'] = 'The default category for auto-created courses. Used when new category id not specified or not found.';
$string['coursetemplate'] = 'New course template';
$string['coursetemplate_desc'] = 'Optional: auto-created courses can copy their settings from a template course. Type here the shortname of the template course.';
$string['updatecourses'] = 'Update courses';
$string['updatecourses_desc'] = 'If enabled courses will be updated from the course table.';
$string['ignorehiddencourses'] = 'Ignore hidden courses';
$string['ignorehiddencourses_desc'] = 'If enabled hidden courses will not be updated.';
$string['settingsheaderdb'] = 'External database connection';
$string['settingsheaderlocal'] = 'Field mapping';
$string['settingsheaderremote'] = 'Remote courses sync';
$string['sync'] = 'Sync courses with external database';
$string['minrecords'] = 'Minimum records';
$string['minrecords_desc'] = 'Prevent the sync from running if the number of records returned in the external table is below this number (helps to prevent removal of users when the external table is empty).';
$string['privacy:metadata'] = 'The Courses database plugin does not store any personal data.';