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

$id = optional_param('id', 0, PARAM_INT); // course_module ID

if ($id) {
    $cm = get_coursemodule_from_id('visualclass', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $visualclass = $DB->get_record('visualclass', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('noiderror', 'visualclass'));
}

require_login($course, true, $cm);
// Deprecated
//$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$context = context_module::instance($cm->id);

/// Print the page header
$PAGE->set_url('/mod/visualclass/report_detailed.php', array('id' => $cm->id));
$PAGE->set_title(format_string($visualclass->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

// Checking what type user is
global $USER, $CFG;

// Loading instance
$visualclass_instance = new mod_visualclass_instance();
$visualclass_instance->set_id($visualclass->id);
$visualclass_instance->read();

// Writing report
// Box start
echo $OUTPUT->box_start();

// Header
echo html_writer::tag('h1', get_string('report_headerdetailed', 'visualclass'));

// Gathering sessions info
$content = array();
$sessions = $visualclass_instance->get_sessions();

// Removing unfinished sessions
$valid_sessions = array();
if (!empty($sessions)) {
    foreach ($sessions as $session) {
        $timestop = $session->get_timestop();
        if (!empty($timestop)) {
            $valid_sessions[] = $session;
        }
    }
}

if (!empty($valid_sessions)) {
    foreach ($valid_sessions as $session) {
        $userid = $session->get_userid();
        $user = user_get_users_by_id(array($userid));
        $user = $user[$userid];
        $username = $user->firstname . ' ' . $user->lastname;

        if (!isset($content[$username])) {
            $content[$username] = array();
        }

        $values = new stdClass();
        $values->attemptnumber = $session->get_attemptnumber();
        $values->time = round(
            ($session->get_timestop() - $session->get_timestart()) / $visualclass_instance::TIME_BASE
        );
        $values->totalscore = $session->get_totalscore();
        $values->items = $session->get_items();

        $content[$username][$session->get_id()] = $values;
    }
    unset($user);
    unset($userid);
    unset($username);
    unset($session);

    // Formatting info
    foreach ($content as $username => $sessions) {
        echo html_writer::tag('h3', $username);
        foreach ($sessions as $session) {
            // Short
            $correct = round(($session->totalscore / 100) * count($session->items));
            $wrong = count($session->items) - $correct;
            $attributes1 = array('style' => 'font-weight: bold; background-color: #282828; color: #E8E8E8;');
            $attributes2 = array('style' => 'color: #FFCC33;');
            echo html_writer::start_tag('p', $attributes1);
            echo get_string('report_attempt', 'visualclass') . '[&nbsp;'
                . html_writer::tag('span', $session->attemptnumber, $attributes2)
                . '&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo get_string('report_percentcorrect', 'visualclass') . '[&nbsp;'
                . html_writer::tag('span', $correct, $attributes2)
                . '&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo get_string('report_percentwrong', 'visualclass') . '[&nbsp;'
                . html_writer::tag('span', $wrong, $attributes2)
                . '&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo get_string('report_totalscore', 'visualclass') . '[&nbsp;'
                . html_writer::tag('span', (int)$session->totalscore, $attributes2)
                . '&nbsp;]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            if ($session->time > 0) {
                echo get_string('report_time', 'visualclass') . '[&nbsp;'
                    . html_writer::tag(
                        'span', $session->time . ' ' . get_string('felem_time_unit', 'visualclass'), $attributes2
                    ) . '&nbsp;]';
            } else {
                echo get_string('report_time', 'visualclass') . '[&nbsp;'
                    . html_writer::tag('span', get_string('report_timezero', 'visualclass'), $attributes2) . '&nbsp;]';
            }
            echo html_writer::end_tag('p');

            if (!empty($session->items)) {
                sort($session->items);

                // Explode
                $html_table = new html_table();
                $html_table->head = array(
                    get_string('report_question', 'visualclass'),
                    get_string('report_pagetitle', 'visualclass'),
                    get_string('report_type', 'visualclass'),
                    get_string('report_answercorrect', 'visualclass'),
                    get_string('report_answeruser', 'visualclass')
                );
                $rows = array();
                foreach ($session->items as $item) {
                    $item_answercorrect = $item->get_answercorrect();
                    $item_answeruser = $item->get_answeruser();
                    if ($item->get_type() == $item::TYPE_TESTEVESTIBULAR) {
                        $item_type = 'Teste Vestibular';

                        // Character parsing
                        $item_answercorrect = (int)$item_answercorrect;
                        switch ($item_answercorrect) {
                        case 1 :
                            $item_answercorrect = 'a';
                            break;
                        case 2 :
                            $item_answercorrect = 'b';
                            break;
                        case 3 :
                            $item_answercorrect = 'c';
                            break;
                        case 4 :
                            $item_answercorrect = 'd';
                            break;
                        case 5 :
                            $item_answercorrect = 'e';
                            break;
                        default:
                            $item_answercorrect = get_string('noanswer', 'visualclass');
                        }

                        $item_answeruser = (int)$item_answeruser;
                        switch ($item_answeruser) {
                        case 1 :
                            $item_answeruser = 'a';
                            break;
                        case 2 :
                            $item_answeruser = 'b';
                            break;
                        case 3 :
                            $item_answeruser = 'c';
                            break;
                        case 4 :
                            $item_answeruser = 'd';
                            break;
                        case 5 :
                            $item_answeruser = 'e';
                            break;
                        default:
                            $item_answeruser = get_string('noanswer', 'visualclass');
                        }
                    } else {
                        $item_type = 'Preenchimento Lacunas';
                        switch ($item->get_type()) {
                        case $item::TYPE_PREENCHIMENTO :
                            $item_type = 'Preenchimento Lacunas';
                            break;
                        case $item::TYPE_ROTULOAVALIAVEL :
                            $item_type = 'Rótulo Avaliável';
                            break;
                        case $item::TYPE_IMAGEMAVALIAVEL :
                            $item_type = 'Imagem Avaliável';
                            break;
                        case $item::TYPE_ARRASTARSOLTARIMAGEM :
                            $item_type = 'Arrastar/Soltar Imagem';
                            break;
                        case $item::TYPE_ARRASTARSOLTAR :
                            $item_type = 'Arrastar/Soltar';
                            break;
                        case $item::TYPE_ARRASTARDIFERENTESOLTAR :
                            $item_type = 'Arrastar≠Soltar';
                            break;
                        case $item::TYPE_GIRAFIGURAS :
                            $item_type = 'Gira Figuras';
                            break;
                        case $item::TYPE_LIGAPONTOS :
                            $item_type = 'Liga Pontos';
                            break;
                        case $item::TYPE_TESTE :
                            $item_type = 'Teste';
                            break;
                        default:
                            $item_type = 'Exercício';
                        }
                        if (is_array($item_answercorrect)) {
                            $item_answercorrect = implode(
                                get_string('report_separator', 'visualclass'), $item_answercorrect
                            );
                        }
                    }

                    if ($item->is_correct()) {
                        $item_status
                            = '<img src="scripts/status_ok.png" alt="correct" style="width: 25px; height: 25px;">';
                    } else {
                        $item_status
                            = '<img src="scripts/status_error.png" alt="wrong" style="width: 25px; height: 25px;">';
                    }

                    $row = array(
                        $item->get_question(),
                        $item->get_pagetitle(),
                        $item_type,
                        $item_answercorrect,
                        $item_answeruser,
                        $item_status
                    );
                    $rows[] = $row;
                }
                $html_table->data = $rows;
                echo html_writer::table($html_table);
            } else {
                echo $OUTPUT->error_text(get_string('error_noitems', 'visualclass'));
            }
        }
    }
} else {
    echo $OUTPUT->error_text(get_string('error_nosessions', 'visualclass'));
}

// Box end
echo $OUTPUT->box_end();

// Finish the page
echo $OUTPUT->footer();
