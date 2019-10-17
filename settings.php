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
 * Plugin settings and presets.
 *
 * @package   tool_coursesdatabase
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {

    // Add a new category under tools.
    $ADMIN->add('tools',
        new admin_category('tool_coursesdatabase', get_string('pluginname', 'tool_coursesdatabase')));

    $settings = new admin_settingpage('tool_coursesdatabase_settings', new lang_string('settings', 'tool_coursesdatabase'),
        'moodle/site:config', false);

    // Add the settings page.
    $ADMIN->add('tool_coursesdatabase', $settings);

    // Add the test settings page.
    $ADMIN->add('tool_coursesdatabase',
            new admin_externalpage('tool_coursesdatabase_test', get_string('testsettings', 'tool_coursesdatabase'),
                $CFG->wwwroot . '/' . $CFG->admin . '/tool/coursesdatabase/test_settings.php'));

    // General settings.
    $settings->add(new admin_setting_heading('tool_coursesdatabase_settings', '',
        get_string('settings_desc', 'tool_coursesdatabase')));

    $settings->add(new admin_setting_heading('tool_coursesdatabase_exdbheader',
        get_string('settingsheaderdb', 'tool_coursesdatabase'), ''));

    $options = array('', "pdo", "pdo_mssql", "pdo_sqlsrv", "access", "ado_access", "ado", "ado_mssql", "borland_ibase",
        "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative", "mysql",
        "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64",
        "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('tool_coursesdatabase/dbtype',
        get_string('dbtype', 'tool_coursesdatabase'),
        get_string('dbtype_desc', 'tool_coursesdatabase'), '', $options));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/dbhost',
        get_string('dbhost', 'tool_coursesdatabase'),
        get_string('dbhost_desc', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/dbuser',
        get_string('dbuser', 'tool_coursesdatabase'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('tool_coursesdatabase/dbpass',
        get_string('dbpass', 'tool_coursesdatabase'), '', ''));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/dbname',
        get_string('dbname', 'tool_coursesdatabase'),
        get_string('dbname_desc', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/dbencoding',
        get_string('dbencoding', 'tool_coursesdatabase'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/dbsetupsql',
        get_string('dbsetupsql', 'tool_coursesdatabase'),
        get_string('dbsetupsql_desc', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configcheckbox('tool_coursesdatabase/dbsybasequoting',
        get_string('dbsybasequoting', 'tool_coursesdatabase'),
        get_string('dbsybasequoting_desc', 'tool_coursesdatabase'), 0));

    $settings->add(new admin_setting_configcheckbox('tool_coursesdatabase/debugdb',
        get_string('debugdb', 'tool_coursesdatabase'),
        get_string('debugdb_desc', 'tool_coursesdatabase'), 0));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/minrecords',
        get_string('minrecords', 'tool_coursesdatabase'),
        get_string('minrecords_desc', 'tool_coursesdatabase'), 1));

    // Field mapping.
    $settings->add(new admin_setting_heading('tool_coursesdatabase_localheader',
        get_string('settingsheaderlocal', 'tool_coursesdatabase'), ''));

    $options = array('id' => 'id', 'idnumber' => 'idnumber', 'shortname' => 'shortname');
    $settings->add(new admin_setting_configselect('tool_coursesdatabase/localcoursefield',
        get_string('localcoursefield', 'tool_coursesdatabase'), '', 'idnumber', $options));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/remotecoursefield', get_string('remotecoursefield', 'tool_coursesdatabase'), get_string('remotecoursefield_desc', 'tool_coursesdatabase'), ''));

    $options = array('id' => 'id', 'idnumber' => 'idnumber');
    $settings->add(new admin_setting_configselect('tool_coursesdatabase/localcategoryfield',
        get_string('localcategoryfield', 'tool_coursesdatabase'), '', 'idnumber', $options));


    // Remote fields.
    $settings->add(new admin_setting_heading('tool_coursesdatabase_remotecoursesheader', get_string('settingsheaderremote', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/remotecoursetable', get_string('remotecoursetable', 'tool_coursesdatabase'), get_string('remotecoursetable_desc', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/coursefullname', get_string('coursefullname', 'tool_coursesdatabase'), '', 'fullname'));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/courseshortname', get_string('courseshortname', 'tool_coursesdatabase'), '', 'shortname'));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/courseidnumber', get_string('courseidnumber', 'tool_coursesdatabase'), '', 'idnumber'));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/coursecategory', get_string('coursecategory', 'tool_coursesdatabase'), '', ''));

    require_once($CFG->dirroot.'/admin/tool/coursesdatabase/settingslib.php');

    $settings->add(new tool_coursesdatabase_admin_setting_category('tool_coursesdatabase/defaultcategory', get_string('defaultcategory', 'tool_coursesdatabase'), get_string('defaultcategory_desc', 'tool_coursesdatabase')));

    $settings->add(new admin_setting_configtext('tool_coursesdatabase/coursetemplate', get_string('coursetemplate', 'tool_coursesdatabase'), get_string('coursetemplate_desc', 'tool_coursesdatabase'), ''));

    $settings->add(new admin_setting_configcheckbox('tool_coursesdatabase/updatecourses', get_string('updatecourses', 'tool_coursesdatabase'), get_string('updatecourses_desc', 'tool_coursesdatabase'), 0));

    $settings->add(new admin_setting_configcheckbox('tool_coursesdatabase/ignorehiddencourses', get_string('ignorehiddencourses', 'tool_coursesdatabase'), get_string('ignorehiddencourses_desc', 'tool_coursesdatabase'), 0));

}
