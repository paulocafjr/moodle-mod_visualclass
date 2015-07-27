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

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('visualclass', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $visualclass = $DB->get_record('visualclass', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('noiderror', 'visualclass'));
}

require_login($course, true, $cm);
$context = context_course::instance($course->id);

// Print the page header.
$PAGE->set_url('/mod/visualclass/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($visualclass->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

// Checking what type user is.
global $USER, $CFG;

// Loading instance.
$activityobj = new mod_visualclass_instance();
$activityobj->set_id($visualclass->id);
$activityobj->read();

// Cancel button action.
$coursepage = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id));

// Setting bookmark.
$sessionid = null;
$pagetitle = null;
$lastpage = null;
$sessions = $activityobj->get_sessions();
if (!empty($sessions)) {
    foreach ($sessions as $session) {
        $timestop = $session->get_timestop();
        if ($session->get_userid() == $USER->id && empty($timestop)) {
            $sessionid = $session->get_id();
            $higher = null;
            $sessionitems = $session->get_items();
            if (!empty($sessionitems)) {
                sort($sessionitems);
                foreach ($sessionitems as $sessionitem) {
                    if (!empty($higher)) {
                        if ($sessionitem->get_id() > $higher->get_id()) {
                            if ($higher->get_pagetitle() !== $sessionitem->get_pagetitle()) {
                                $lastpage = $higher->get_pagetitle();
                            }
                            $higher = $sessionitem;
                            $pagetitle = $sessionitem->get_pagetitle();
                        }
                    } else {
                        $higher = $sessionitem;
                    }
                }
            }
        }
    }
}

// Refreshing instance.
$activityobj->read();

if (has_capability('mod/visualclass:reports', $context, $USER->id)) {
    // Showing admin options.
    $urlreportparams = array('id' => $id);
    $urlreportparamsuser = array('cmid' => $id, 'type' => mod_visualclass_instance::REPORT_USER);
    $urlreportparamsquestion = array('cmid' => $id, 'type' => mod_visualclass_instance::REPORT_QUESTION);
    $urlreport1 = new moodle_url('/mod/visualclass/report_detailed.php', $urlreportparams);
    $urlreport2 = new moodle_url('/mod/visualclass/report_question.php', $urlreportparams);
    $urlreport3 = new moodle_url('/mod/visualclass/export_xlsx.php', $urlreportparamsuser);
    $urlreport4 = new moodle_url('/mod/visualclass/export_xlsx.php', $urlreportparamsquestion);
    $urlproject = new moodle_url($activityobj->get_projecturl());

    // View Report by student.
    echo $OUTPUT->heading(get_string('text_adminprivileges1', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($urlreport1, array()));
    // View Report by question.
    echo $OUTPUT->heading(get_string('text_adminprivileges2', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($urlreport2, array()));
    // Export Report by student.
    echo $OUTPUT->heading(get_string('text_adminprivileges3', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($urlreport3, array()));
    // Export Report by question.
    echo $OUTPUT->heading(get_string('text_adminprivileges4', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($urlreport4, array()));
    // View Project.
    echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
    echo $OUTPUT->continue_button(new moodle_url($urlproject, array()));

} else if (!has_capability('mod/visualclass:view', $context, $USER->id)) {
    $message = get_string('error_nocapability', 'visualclass');
    echo $OUTPUT->error_text($message);
} else {
    if (!has_capability('mod/visualclass:submit', $context, $USER->id)) {
        $urlproject = new moodle_url($activityobj->get_projecturl());
        echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
        echo $OUTPUT->continue_button(new moodle_url($urlproject, array()));
    } else {
        // Show activity.
        $attemptnumber = (int)$activityobj->get_nextattemptnumber($USER->id);
        $policyattempts = (int)$activityobj->get_policyattempts();

        $condition1 = $policyattempts !== $activityobj::ATTEMPT_UNLIMITED ? true : false;

        $condition2 = $attemptnumber > $policyattempts && empty($sessionid) ? true : false;

        if ($condition1 && $condition2) {
            $message = get_string('error_maxattemptsreached', 'visualclass');
            echo $OUTPUT->error_text($message);
        } else {
            // Creating a session for this user in this activity.
            $url = $activityobj->get_projecturl();
            $params = '?userid=' . md5($USER->id);
            if (!empty($sessionid)) {
                $activitysession = new mod_visualclass_session();
                $activitysession->set_id($sessionid);
                $activitysession->read();
                $activitysession->set_timestart(time());
                $activitysession->write();
            } else {
                $activitysession = new mod_visualclass_session();
                $activitysession->set_userid($USER->id);
                $activitysession->set_modid($visualclass->id);
                $activitysession->set_attemptnumber($attemptnumber);
                $activitysession->set_timestart(time());
                $activitysession->write();

                $sessionid = $activitysession->get_id();
            }

            $_SESSION[$activityobj::SESSION_PREFIX . $USER->id] = $sessionid;

            // Showing Activity.
            $url .= $params;
            switch ($activityobj->get_policyview()) {
            case $activityobj::VIEW_MOODLE:
                $iframe = '<iframe src="' . $url
                    . '" seamless="seamless" style="width:100%;height:768px;">'
                    . '</iframe>';
                echo $OUTPUT->box($iframe);
                break;
            case $activityobj::VIEW_NEWTAB:
                echo $OUTPUT->heading(get_string('text_gotoproject', 'visualclass'), 5);
                echo $OUTPUT->continue_button(new moodle_url($url, array()));
                break;
            case $activityobj::VIEW_POPUP:
                $width = $visualclass->policyview_width;
                $height = $visualclass->policyview_height;
                $popupparams = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,"
                    ."status=no,directories=no,scrollbars=yes,resizable=yes";
                $js = "onclick=\"window.open('$url', '', '$popupparams'); return false;\"";
                echo '<div class="urlworkaround">';
                print_string('text_popup', 'visualclass');
                $buttonmessage = get_string('text_popup_view', 'visualclass');
                echo "<br><a href=\"$url\" $js>$buttonmessage</a>";
                echo '</div>';
                break;
            default:
                echo $OUTPUT->error_text(get_string('error_unknown', 'visualclass'));
            }
        }
    }
}


// Finish the page.
echo $OUTPUT->footer();

session_write_close();