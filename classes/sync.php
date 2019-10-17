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
 * Course database sync plugin.
 *
 * This plugin synchronises courses with external database table.
 *
 * @package   tool_coursesdatabase
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

/**
 * coursesdatabase tool class
 *
 * @package   tool_coursesdatabase
 * @copyright 2019 Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_coursesdatabase_sync {

    /**
     * @var stdClass config for this plugin
     */
    protected $config;

    /**
     * Performs a full sync with external database.
     *
     * @param progress_trace $trace
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     */
    public function sync(progress_trace $trace) {
        global $DB;

        $this->config = get_config('tool_coursesdatabase');

        // Check if it is configured.
        if (empty($this->config->dbtype) || empty($this->config->dbhost)) {
            $trace->finished();
            return 1;
        }

        $trace->output('Starting course synchronisation...');

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        // Set some vars for better code readability.
        $table              = trim($this->config->remotecoursetable);
        $localcoursefield   = trim($this->config->localcoursefield);
        $remotecoursefield  = strtolower(trim($this->config->remotecoursefield));
        $localcategoryfield = $this->config->localcategoryfield;
        $coursefullname     = strtolower(trim($this->config->coursefullname));
        $courseshortname    = strtolower(trim($this->config->courseshortname));
        $courseidnumber     = strtolower(trim($this->config->courseidnumber));
        $coursecategory     = strtolower(trim($this->config->coursecategory));
        $coursetemplate     = strtolower(trim($this->config->coursetemplate));
        $updatecourses       = $this->config->updatecourses;
        $ignorehidden       = $this->config->ignorehiddencourses;
        $defaultcategory    = $this->config->defaultcategory;

        if (empty($coursefullname)) {
            $trace->output('Plugin config not complete.');
            $trace->finished();
            return 1;
        }

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external courses database');
            $trace->finished();
            return 1;
        }

        // Sanity check - make sure external table has the expected number of records before we trigger the sync.
        $hasenoughrecords = false;
        $count = 0;
        $minrecords = $this->config->minrecords;
        if (!empty($minrecords)) {
            $sql = "SELECT count(*) FROM $table";
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    while ($fields = $rs->FetchRow()) {
                        $count = array_pop($fields);
                        if ($count > $minrecords) {
                            $hasenoughrecords = true;
                        }
                    }
                }
            }
        }
        if (!$hasenoughrecords) {
            //$trace->output($extdb->ErrorMsg());
            $trace->output("Failed to sync because the external db returned $count records and the minimum required is $minrecords");
            $trace->finished();
            return 1;
        }

        // Get courses from the external database.
        $trace->output('Starting course database sync');
        $sql = $this->db_get_sql($table);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);

                    // Check that all the required fields are present.
                    if (empty($fields[$remotecoursefield]) || empty($fields[$coursefullname]) || empty($fields[$courseshortname])) {
                        $trace->output('error: invalid external course record, one or more required fields are empty: '
                            . json_encode($fields), 1);
                        continue;
                    }

                    $cat = $defaultcategory;
                    if ($coursecategory) {
                        if ($category = $DB->get_record('course_categories', array($localcategoryfield=>$fields[$coursecategory]), 'id')) {
                            // Yay, correctly specified category!
                            $cat = $category->id;
                            unset($category);
                        } else {
                            // Bad luck, better not continue because unwanted ppl might get access to course in different category.
                            $trace->output('error: invalid category '.$fields[$coursecategory].', can not create/update course: '.$fields[$courseshortname], 1);
                            continue;
                        }
                    }

                    // Check whether the course exists.
                    $course = $DB->get_record('course', array($localcoursefield => $fields[$remotecoursefield]), 'id,visible,shortname');
                    if (empty($course)) {
                        // Create new course
                        $trace->output("Caching course for creation: $fields[$courseshortname].", 1);
                        $course = new stdClass();
                        $course->fullname  = $fields[$coursefullname];
                        $course->shortname = $fields[$courseshortname];
                        $course->idnumber  = $courseidnumber ? $fields[$courseidnumber] : '';
                        $course->category  = $cat;
                        $course->template  = $coursetemplate ? $fields[$coursetemplate] : '';
                        $createcourses[] = $course;
                    } else {
                        // Update existing course.
                        if (!$updatecourses) {
                            $trace->output("error: skipping update to $course->shortname ($course->id) because update is disabled in config.", 1);
                            continue;
                        }
                        if ($ignorehidden and !$course->visible) {
                            $trace->output("error: skipping row because course $localcoursefield
                            '$fields[$coursefield]' is hidden.", 1);
                            continue;
                        }
                        $trace->output('Updating course: '.$fields[$courseshortname], 1);
                        $course->fullname  = $fields[$coursefullname];
                        $course->shortname = $fields[$courseshortname];
                        $course->idnumber  = $courseidnumber ? $fields[$courseidnumber] : '';
                        if ($coursecategory && isset($fields[$coursecategory])) {
                            $course->category  = $cat;
                        }
                        $DB->update_record('course', $course);
                    }
                }
            }
        } else {
            $extdb->Close();
            $trace->output('Error reading data from the external course table');
            $trace->finished();
            return 4;
        }

        if (count($createcourses)) {
            $trace->output("Creating courses...");
            foreach ($createcourses as $fields) {
                $template = $this->get_template($fields->template);
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category;

                // Detect duplicate data once again, above we can not find duplicates
                // in external data using DB collation rules...
                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    $trace->output("can not insert new course, duplicate shortname detected: $newcourse->shortname", 1);
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    $trace->output("can not insert new course, duplicate idnumber detected: $newcourse->idnumber", 1);
                    continue;
                }
                $c = create_course($newcourse);
                $trace->output("created course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category", 1);
                unset($template);
            }
            unset($createcourses);
        }

        $extdb->Close();
        $trace->output("Sync complete");
        $trace->finished();

        return 0;
    }

    protected function get_template($shortname) {
        global $DB;

        $template = false;

        // Attempt to get the template first.
        if ($shortname) {
            if ($template = $DB->get_record('course', array('shortname'=>$shortname))) {
                $template = fullclone(course_get_format($template)->get_course());
                if (!isset($template->numsections)) {
                    $template->numsections = course_get_format($template)->get_last_section_number();
                }
                unset($template->id);
                unset($template->fullname);
                unset($template->shortname);
                unset($template->idnumber);
            }
        }
        // If template not specified or not found, create a fresh one.
        if (!$template) {
            $courseconfig = get_config('moodlecourse');
            $template = new stdClass();
            $template->summary        = '';
            $template->summaryformat  = FORMAT_HTML;
            $template->format         = $courseconfig->format;
            $template->numsections    = $courseconfig->numsections;
            $template->newsitems      = $courseconfig->newsitems;
            $template->showgrades     = $courseconfig->showgrades;
            $template->showreports    = $courseconfig->showreports;
            $template->maxbytes       = $courseconfig->maxbytes;
            $template->groupmode      = $courseconfig->groupmode;
            $template->groupmodeforce = $courseconfig->groupmodeforce;
            $template->visible        = $courseconfig->visible;
            $template->lang           = $courseconfig->lang;
            $template->enablecompletion = $courseconfig->enablecompletion;
            $template->groupmodeforce = $courseconfig->groupmodeforce;
            $template->startdate      = usergetmidnight(time());
            if ($courseconfig->courseenddateenabled) {
                $template->enddate    = usergetmidnight(time()) + $courseconfig->courseduration;
            }
        }

        return $template;
    }

    /**
     * Test plugin settings, print info to output.
     */
    public function test_settings() {
        global $CFG, $OUTPUT;

        // NOTE: this is not localised intentionally, admins are supposed to understand English at least a bit...

        raise_memory_limit(MEMORY_HUGE);

        $this->config = get_config('tool_coursesdatabase');

        $table = $this->config->remotecoursetable;

        if (empty($table)) {
            echo $OUTPUT->notification('External course table not specified.', 'notifyproblem');
            return;
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugdb = $this->config->debugdb;
        $this->config->debugdb = 1;
        error_reporting($CFG->debug);

        $adodb = $this->db_init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->config->debugdb = $olddebugdb;
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification('Cannot connect the database.', 'notifyproblem');
            return;
        }

        if (!empty($table)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $table");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external course table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External course table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification('External course table contains following columns:<br />'.
                    implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $adodb->Close();

        $this->config->debugdb = $olddebugdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }

    /**
     * Tries to make connection to the external database.
     *
     * @return null|ADONewConnection
     */
    public function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        // Connect to the external database (forcing new connection).
        $extdb = ADONewConnection($this->config->dbtype);
        if ($this->config->debugdb) {
            $extdb->debug = true;
            ob_start(); // Start output buffer to allow later use of the page headers.
        }

        // The dbtype my contain the new connection URL, so make sure we are not connected yet.
        if (!$extdb->IsConnected()) {
            $result = $extdb->Connect($this->config->dbhost, $this->config->dbuser, $this->config->dbpass,
                $this->config->dbname, true);
            if (!$result) {
                return null;
            }
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->config->dbsetupsql) {
            $extdb->Execute($this->config->dbsetupsql);
        }
        return $extdb;
    }

    /**
     * Encode text.
     *
     * @param string $text
     * @return string
     */
    protected function db_encode($text) {
        $dbenc = $this->config->dbencoding;
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach ($text as $k => $value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, 'utf-8', $dbenc);
        }
    }

    /**
     * Decode text.
     *
     * @param string $text
     * @return string
     */
    protected function db_decode($text) {
        $dbenc = $this->config->dbencoding;
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach ($text as $k => $value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, $dbenc, 'utf-8');
        }
    }

    /**
     * Generate SQL required based on params.
     *
     * @param string $table - name of table
     * @param array $conditions - conditions for select.
     * @param array $fields - fields to return
     * @param boolean $distinct
     * @param string $sort
     * @return string
     */
    protected function db_get_sql($table, $conditions = array(), $fields = array(), $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key => $value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    /**
     * Add slashes to text.
     *
     * @param string $text
     * @return string
     */
    protected function db_addslashes($text) {
        // Use custom made function for now - it is better to not rely on adodb or php defaults.
        if ($this->config->dbsybasequoting) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }
}

