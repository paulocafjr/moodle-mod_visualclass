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
 * View for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática  Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

if (!isset($_SESSION)) {
    session_name('MoodleSession');
    session_start();
}

$id = optional_param('id', 0, PARAM_INT); // course_module ID

if ($id) {
    $cm = get_coursemodule_from_id('visualclass', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $visualclass = $DB->get_record('visualclass', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('noiderror', 'visualclass'));
}

require_login($course, true, $cm);
$context = context_course::instance($course->id);

/// Print the page header
$PAGE->set_url('/mod/visualclass/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($visualclass->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here
echo $OUTPUT->header();

// Checking what type user is
global $USER, $CFG;

// Loading instance
$visualclass_instance = new mod_visualclass_instance();
$visualclass_instance->set_id($visualclass->id);
$visualclass_instance->read();

// Cancel button action
$coursepage = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id));

// Setting bookmark
$sessionid = null;
$pagetitle = null;
$sessions = $visualclass_instance->get_sessions();
if (!empty($sessions)) {
    foreach ($sessions as $session) {
        $timestop = $session->get_timestop();
        if ($session->get_userid() == $USER->id && empty($timestop)) {
            $sessionid = $session->get_id();
            $higher = 0;
            $sessionitems = $session->get_items();
            if (!empty($sessionitems)) {
                foreach ($sessionitems as $sessionitem) {
                    if ($sessionitem->get_id() > $higher) {
                        $higher = $sessionitem->get_id();
                        $pagetitle = $sessionitem->get_pagetitle();
                    }
                }
            }
        }
    }
}

// Refreshing instance
$visualclass_instance->read();

if (has_capability('mod/visualclass:reports', $context, $USER->id)) {
    // Showing admin options
    $url_report_params = array('id' => $id);
    $url_report_1 = new moodle_url('/mod/visualclass/report_detailed.php', $url_report_params);
    $url_report_2 = new moodle_url('/mod/visualclass/report_question.php', $url_report_params);
    $url_project = new moodle_url($visualclass_instance->get_projecturl());

    // View Report by student
    echo $OUTPUT->confirm(get_string('text_adminprivileges1', 'visualclass'), $url_report_1, $coursepage);
    // View Report by question
    echo $OUTPUT->confirm(get_string('text_adminprivileges2', 'visualclass'), $url_report_2, $coursepage);
    // View Project
    echo $OUTPUT->confirm(get_string('text_gotoproject', 'visualclass'), $url_project, $coursepage);

} else if (!has_capability('mod/visualclass:view', $context, $USER->id)) {
    $message = get_string('error_nocapability', 'visualclass');
    echo $OUTPUT->error_text($message);
} else {
    if (!has_capability('mod/visualclass:submit', $context, $USER->id)) {
        $url_project = new moodle_url($visualclass_instance->get_projecturl());
        echo $OUTPUT->confirm(get_string('text_gotoproject', 'visualclass'), $url_project, $coursepage);
    } else {
        // Show activity
        $attemptnumber = (int)$visualclass_instance->get_nextattemptnumber($USER->id);
        $policyattempts = (int)$visualclass_instance->get_policyattempts();

        $condition1 = $policyattempts
        !== $visualclass_instance::ATTEMPT_UNLIMITED ? true : false;

        $condition2 = $attemptnumber > $policyattempts ? true : false;

        if ($condition1 && $condition2) {
            $message = get_string('error_maxattemptsreached', 'visualclass');
            echo $OUTPUT->error_text($message);
        } else {
            // Creating a session for this user in this activity
            $url = $visualclass_instance->get_projecturl();
            if (!empty($sessionid)) {
                if (!empty($pagetitle)) {
                    $url .= $pagetitle;
                }
                $visualclass_session = new mod_visualclass_session();
                $visualclass_session->set_id($sessionid);
                $visualclass_session->read();
                $visualclass_session->set_timestart(time());
                $visualclass_session->write();
            } else {
                $visualclass_session = new mod_visualclass_session();
                $visualclass_session->set_userid($USER->id);
                $visualclass_session->set_modid($visualclass->id);
                $visualclass_session->set_attemptnumber($attemptnumber);
                $visualclass_session->set_timestart(time());
                $visualclass_session->write();

                $sessionid = $visualclass_session->get_id();
            }

            $_SESSION[$visualclass_instance::SESSION_PREFIX . $USER->id] = $sessionid;

            // Showing Activity
            switch ($visualclass_instance->get_policyview()) {
            case $visualclass_instance::VIEW_MOODLE:
                $iframe = '<iframe src="' . $url
                    . '" seamless="seamless" style="width:100%;height:768px;">'
                    . '</iframe>';
                echo $OUTPUT->box($iframe);
                break;
            case $visualclass_instance::VIEW_NEWTAB:
                echo $OUTPUT->confirm(
                    get_string('text_gotoproject', 'visualclass'),
                    $url, $coursepage
                );
                break;
            default:
                echo $OUTPUT->error_text(get_string('error_unknown', 'visualclass'));
            }
        }
    }
}


// Finish the page
echo $OUTPUT->footer();

session_write_close();