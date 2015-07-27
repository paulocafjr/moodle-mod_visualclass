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
 * Detailed report generator for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática  Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/user/lib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('visualclass', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $visualclass = $DB->get_record('visualclass', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('noiderror', 'visualclass'));
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Print the page header.
$PAGE->set_url('/mod/visualclass/report_detailed.php', array('id' => $cm->id));
$PAGE->set_title(format_string($visualclass->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();

// Checking what type user is.
global $USER, $CFG;

// Loading instance.
$activityobj = new mod_visualclass_instance();
$activityobj->set_id($visualclass->id);
$activityobj->read();

// Writing report
// Box start.
echo $OUTPUT->box_start();

// Header.
echo html_writer::tag('h1', get_string('report_headerquestion', 'visualclass'));

// Gathering sessions info.
$content = array();
$sessions = $activityobj->get_sessions();

// Removing unfinished sessions.
$validsessions = array();
if (!empty($sessions)) {
    foreach ($sessions as $session) {
        $timestop = $session->get_timestop();
        if (!empty($timestop)) {
            $validsessions[] = $session;
        }
    }
}

if (!empty($validsessions)) {
    $percent = array();
    foreach ($validsessions as $session) {
        $userid = $session->get_userid();
        $user = user_get_users_by_id(array($userid));
        $user = $user[$userid];
        $username = $user->firstname . ' ' . $user->lastname;

        if (!isset($content[$username])) {
            $content[$username] = array();
        }

        $values = new stdClass();
        $values->timestop = $session->get_timestop();
        $values->totalscore = $session->get_totalscore();
        $values->items = $session->get_items();

        switch ($activityobj->get_policygrades()) {
        case mod_visualclass_instance::GRADE_BEST:
            if (!isset($content[$username][0])) {
                $content[$username][0] = $values;
            } else {
                if ($content[$username][0]->totalscore < $values->totalscore) {
                    $content[$username][0] = $values;
                }
            }
            break;
        default:
            if (!isset($content[$username][0])) {
                $content[$username][0] = $values;
            } else {
                if ($content[$username][0]->timestop < $values->timestop) {
                    $content[$username][0] = $values;
                }
            }
        }
    }

    foreach ($content as $username => $values) {
        $items = $values[0]->items;
        if (!empty($items)) {
            sort($items);
            foreach ($items as $item) {
                if (isset($percent[$item->get_question()])) {
                    $percent[$item->get_question()]['count'] += 1;
                    $percent[$item->get_question()]['correct'] += $item->is_correct() ? 1 : 0;
                    $percent[$item->get_question()]['wrong'] += $item->is_correct() ? 0 : 1;
                } else {
                    $percent[$item->get_question()] = array(
                        'count' => 1,
                        'correct' => $item->is_correct() ? 1 : 0,
                        'wrong' => $item->is_correct() ? 0 : 1
                    );
                }
            }
        } else {
            echo $OUTPUT->error_text(get_string('error_noitems', 'visualclass'));
        }
    }

    // Explode.
    $htmltable = new html_table();
    $htmltable->head = array(
        get_string('report_question', 'visualclass'),
        get_string('report_percentcorrect', 'visualclass'),
        get_string('report_percentwrong', 'visualclass'),
        get_string('report_percenttotal', 'visualclass'),
        get_string('report_percent', 'visualclass')
    );
    $rows = array();
    foreach ($percent as $question => $values) {
        $row = array(
            $question,
            $values['correct'],
            $values['wrong'],
            $values['count'],
            round($values['correct'] * 100 / $values['count']) . '%'
        );
        $rows[] = $row;
    }
    $htmltable->data = $rows;
    echo html_writer::table($htmltable);
} else {
    echo $OUTPUT->error_text(get_string('error_nosessions', 'visualclass'));
}

// Box end.
echo $OUTPUT->box_end();

// Finish the page.
echo $OUTPUT->footer();