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
    $url_report_params_user = array('cmid' => $id, 'type' => mod_visualclass_instance::REPORT_USER);
    $url_report_params_question = array('cmid' => $id, 'type' => mod_visualclass_instance::REPORT_QUESTION);
    $url_report_1 = new moodle_url('/mod/visualclass/report_detailed.php', $url_report_params);
    $url_report_2 = new moodle_url('/mod/visualclass/report_question.php', $url_report_params);
    $url_report_3 = new moodle_url('/mod/visualclass/export_xlsx.php', $url_report_params_user);
    $url_report_4 = new moodle_url('/mod/visualclass/export_xlsx.php', $url_report_params_question);
    $url_project = new moodle_url($visualclass_instance->get_projecturl());

    // View Report by student
    echo $OUTPUT->heading(get_string('text_adminprivileges1', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($url_report_1, array()));
    // View Report by question
    echo $OUTPUT->heading(get_string('text_adminprivileges2', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($url_report_2, array()));
    // Export Report by student
    echo $OUTPUT->heading(get_string('text_adminprivileges3', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($url_report_3, array()));
    // Export Report by question
    echo $OUTPUT->heading(get_string('text_adminprivileges4', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($url_report_4, array()));
    // View Project
    echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($url_project, array()));

} else if (!has_capability('mod/visualclass:view', $context, $USER->id)) {
    $message = get_string('error_nocapability', 'visualclass');
    echo $OUTPUT->error_text($message);
} else {
    if (!has_capability('mod/visualclass:submit', $context, $USER->id)) {
        $url_project = new moodle_url($visualclass_instance->get_projecturl());
        echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
        echo $OUTPUT->continue_button(new moodle_url($url_project, array()));
    } else {
        // Show activity
        $attemptnumber = (int)$visualclass_instance->get_nextattemptnumber($USER->id);
        $policyattempts = (int)$visualclass_instance->get_policyattempts();

        $condition1 = $policyattempts
        !== $visualclass_instance::ATTEMPT_UNLIMITED ? true : false;

        $condition2 = $attemptnumber > $policyattempts && empty($sessionid) ? true : false;

        if ($condition1 && $condition2) {
            $message = get_string('error_maxattemptsreached', 'visualclass');
            echo $OUTPUT->error_text($message);
        } else {
            // Creating a session for this user in this activity
            $url = $visualclass_instance->get_projecturl();
            if (!empty($sessionid)) {
//                if (!empty($pagetitle)) {
//                    if (!strstr($pagetitle, '.htm')) {
//                        $pagetitle .= '.htm';
//                    }
//                    $url .= $pagetitle;
//                }
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
                echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
                echo $OUTPUT->continue_button(new moodle_url($url, array()));
                break;
            case $visualclass_instance::VIEW_POPUP:
                $width = $visualclass->policyview_width;
                $height = $visualclass->policyview_height;
                $popup_params = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
                $js = "onclick=\"window.open('$url', '', '$popup_params'); return false;\"";
                echo '<div class="urlworkaround">';
                print_string('text_popup', 'visualclass');
                $button_message = get_string('text_popup_view', 'visualclass');
                echo "<br><a href=\"$url\" $js>$button_message</a>";
                echo '</div>';
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