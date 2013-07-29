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
 * Moodle API integration for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require(dirname(__FILE__) . '/locallib.php');

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 *
 * @param string $feature FEATURE_xx constant for requested feature
 *
 * @return mixed true if the feature is supported, null if unknown
 */
function visualclass_supports($feature)
{
    switch ($feature) {
    case FEATURE_MOD_INTRO:
        return false;
    default:
        return null;
    }
}

/**
 * Saves a new instance of the visualclass into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object                   $visualclass An object from the form in mod_form.php
 * @param mod_visualclass_mod_form $mform
 *
 * @return int The id of the newly inserted visualclass record
 */
function visualclass_add_instance(stdClass $visualclass, mod_visualclass_mod_form $mform)
{
    global $DB;

    $visualclass_instance = new mod_visualclass_instance();
    $visualclass_instance->set_course($visualclass->course);
    $visualclass_instance->set_name($visualclass->name);
    //$visualclass_instance->set_projectsubject($visualclass->projectsubject);
    $visualclass_instance->set_policyattempts($visualclass->policyattempts);
    //$visualclass_instance->set_policytime($visualclass->policytime);
    $visualclass_instance->set_policytime($visualclass_instance::TIME_UNLIMITED);
    $visualclass_instance->set_policygrades($visualclass->policygrades);
    $visualclass_instance->set_policyview($visualclass->policyview);

    $visualclass_instance->write_projectdata(
        $visualclass->course,
        $visualclass->file
    );
    $visualclass_instance->write_projecturl($visualclass->course);

    $visualclass_instance->write();
    return $visualclass_instance->get_id();
}

/**
 * Updates an instance of the visualclass in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object                   $visualclass An object from the form in mod_form.php
 * @param mod_visualclass_mod_form $mform
 *
 * @return boolean Success/Fail
 */
function visualclass_update_instance(stdClass $visualclass, mod_visualclass_mod_form $mform = null)
{
    global $DB;

    $visualclass_instance = new mod_visualclass_instance();
    $visualclass_instance->set_id($visualclass->instance);

    $visualclass_instance->read();

    $visualclass_instance->set_course($visualclass->course);
    $visualclass_instance->set_name($visualclass->name);
    //$visualclass_instance->set_projectsubject($visualclass->projectsubject);
    $visualclass_instance->set_policyattempts($visualclass->policyattempts);
    $visualclass_instance->set_policytime($visualclass->policytime);
    $visualclass_instance->set_policygrades($visualclass->policygrades);
    $visualclass_instance->set_policyview($visualclass->policyview);

    $visualclass_instance->write();
    return true;
}

/**
 * Removes an instance of the visualclass from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 *
 * @return boolean Success/Failure
 */
function visualclass_delete_instance($id)
{
    global $DB;

    $visualclass_instance = new mod_visualclass_instance();
    $visualclass_instance->set_id($id);

    $visualclass_instance->read();

    $visualclass_instance->delete_projectdata();
    $visualclass_instance->delete_projecturl();
    return $visualclass_instance->delete();
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function visualclass_user_outline($course, $user, $mod, $visualclass)
{
    return null;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course      the current course record
 * @param stdClass $user        the record of the user we are generating report for
 * @param cm_info  $mod         course module info
 * @param stdClass $visualclass the module instance record
 *
 * @return void, is supposed to echp directly
 */
function visualclass_user_complete($course, $user, $mod, $visualclass)
{
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in visualclass activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function visualclass_print_recent_activity($course, $viewfullnames, $timestart)
{
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link visualclass_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int   $index      the index in the $activities to use for the next record
 * @param int   $timestart  append activity since this time
 * @param int   $courseid   the id of the course we produce the report for
 * @param int   $cmid       course module id
 * @param int   $userid     check for a particular user's activity only, defaults to 0 (all users)
 * @param int   $groupid    check for a particular group's activity only, defaults to 0 (all groups)
 *
 * @return void adds items into $activities and increases $index
 */
function visualclass_get_recent_mod_activity(
    &$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0
)
{
}

/**
 * Prints single activity item prepared by {@see visualclass_get_recent_mod_activity()}
 *
 * @return void
 */
function visualclass_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames)
{
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function visualclass_cron()
{
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function visualclass_get_extra_capabilities()
{
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of visualclass?
 *
 * This function returns if a scale is being used by one visualclass
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $visualclassid ID of an instance of this module
 *
 * @return bool true if the scale is used by the given visualclass instance
 */
function visualclass_scale_used($visualclassid, $scaleid)
{
    return false;
}

/**
 * Checks if scale is being used by any instance of visualclass.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 *
 * @return boolean true if the scale is used by any visualclass instance
 */
function visualclass_scale_used_anywhere($scaleid)
{
    return false;
}

/**
 * Creates or updates grade item for the give visualclass instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $visualclass instance object with extra cmidnumber and modname property
 *
 * @return void
 */
function visualclass_grade_item_update(stdClass $visualclass, $grades = null)
{
    global $CFG;

    require_once($CFG->dirroot . '/lib/gradelib.php');

    $params = array(
        'itemname' => $visualclass->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => 100,
        'grademin' => 0
    );

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/visualclass', $visualclass->course, 'mod',
        'visualclass', $visualclass->id, 0, $grades, $params
    );
}

/**
 * Update visualclass grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $visualclass instance object with extra cmidnumber and modname property
 * @param int      $userid      update grade of specific user only, 0 means all participants
 *
 * @return void
 */
function visualclass_update_grades(stdClass $visualclass, $userid = 0)
{
    return false;
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 *
 * @return array of [(string)filearea] => (string)description
 */
function visualclass_get_file_areas($course, $cm, $context)
{
    return array();
}

/**
 * File browsing support for visualclass file areas
 *
 * @package  mod_visualclass
 * @category files
 *
 * @param file_browser $browser
 * @param array        $areas
 * @param stdClass     $course
 * @param stdClass     $cm
 * @param stdClass     $context
 * @param string       $filearea
 * @param int          $itemid
 * @param string       $filepath
 * @param string       $filename
 *
 * @return file_info instance or null if not found
 */
function visualclass_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename)
{
    return null;
}

/**
 * Serves the files from the visualclass file areas
 *
 * @package  mod_visualclass
 * @category files
 *
 * @param stdClass $course        the course object
 * @param stdClass $cm            the course module object
 * @param stdClass $context       the visualclass's context
 * @param string   $filearea      the name of the file area
 * @param array    $args          extra arguments (itemid, path)
 * @param bool     $forcedownload whether or not force download
 * @param array    $options       additional options affecting the file serving
 */
function visualclass_pluginfile(
    $course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()
)
{
    global $DB, $CFG;

    require_login($course, true, $cm);

    $haystack = visualclass_get_file_areas($course, $cm, $context);
    if (!in_array($filearea, $haystack)) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $component = 'mod_visualclass';
    // gets the last element of $args
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
    // 0 means no filter is being applied
    $filter = 0;
    $file = $fs->get_file($context->id, $component, $filearea, $filter, $filepath, $filename);

    if ($file) {
        $lifetime = $CFG->filelifetime ? $CFG->filelifetime : 86400; //24 hours
        send_stored_file($file, $lifetime, $filter, $forcedownload, $options);
    }
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding visualclass nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the visualclass module instance
 * @param stdClass        $course
 * @param stdClass        $module
 * @param cm_info         $cm
 */
function visualclass_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm)
{
}

/**
 * Extends the settings navigation with the visualclass settings
 *
 * This function is called when the context for the page is a visualclass module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav     {@link settings_navigation}
 * @param navigation_node     $visualclassnode {@link navigation_node}
 */
function visualclass_extend_settings_navigation(
    settings_navigation $settingsnav, navigation_node $visualclassnode = null
)
{
}
