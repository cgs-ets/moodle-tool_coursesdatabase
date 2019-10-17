# Course external database sync tool for Moodle
[![Build Status](https://travis-ci.org/cgs-ets/moodle-tool_coursesdatabase.svg?branch=master)](https://travis-ci.org/cgs-ets/moodle-tool_coursesdatabase)

This plugin syncs courses using an external database table.

Author
--------
Michael Vangelovski, Canberra Grammar School <michael.vangelovski@cgs.act.edu.au>


Installation
------------

1. Download the plugin or install it by using git to clone it into your source:

   ```sh
   git clone git@github.com:cgs-ets/moodle-tool_coursesdatabase.git admin/tool/coursesdatabase
   ```

2. Then run the Moodle upgrade

External database requirements
------------------------------
Only a single table/view is required in the external database which contains a record for each course. If the table is large it is a good idea to make sure appropriate indexes have been created. The table/view must have the following minimum fields: 

* A unique course identifier to match 
  * the "idnumber" field in Moodle's course table (varchar 100), which is manually specified as the "Course ID number" when editing a course's settings
  * the "shortname" field in Moodle's course table (varchar 255), which is manually specified as the "Course short name" when editing a course's settings
  * the "id" field in Moodle's course table (int 10), which is based on course creation order
* The fullname for the course
* The shortname for the course
* (Optional) The idnumber for the course
* (Optional) The idnumber of the category to place the course into
* (Optional) The shortname of the template course to use.

Setting up the course sync (How to)
-----------------------------------
In Moodle, go to Site administration > Plugins > Admin tools > Courses external database > Settings.

* In the top panel, select the database type (make sure you have the necessary configuration in PHP for that type) and then supply the information to connect to the database.
* localcoursefield - in Moodle the name of the field that uniquely identifies the course (e.g., idnumber).
* remotecoursefield - the name of the column in the external database table that uniquiely identifies the course.
* localcategoryfield - in Moodle the name of the field that uniquely identifies the category (e.g., idnumber).
* remotecoursetable - the name of the remote table/view.
* coursefullname - the name of the column in the external database table that contains the course fullname.
* courseshortname - the name of the column in the external database table that contains the course shortname.
* courseidnumber - the name of the column in the external database table that contains the course idnumber.
* coursecategory - the name of the column in the external database table that contains the course category.
* defaultcategory - the default category for auto-created courses. Used when category id not specified or not found.
* coursetemplate - the name of the column in the external database table that contains the course template shortname.
* updatecourses - Select whether to update existing courses. This will overwrite the course fullname, shortname, idnumber and category.
* ignorehiddencourses - If enabled hidden courses will not be updated.


Courses that exist in Moodle but not found in the table will not be deleted. 

The edit the cron schedule, go to Site administration > Server > Scheduled tasks and edit the task named "Sync courses with external database".

Support
-------

If you have issues please log them in github here:
https://github.com/cgs-ets/moodle-tool_coursesdatabase/issues

