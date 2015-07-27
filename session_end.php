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
 * Ends session for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $USER, $CFG;

$finalscore = (int)$_REQUEST['finalscore'];
if (isset($_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id])) {
    $sessionid = $_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id];
    unset($_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id]);

    // Recovering visualclass session.
    $activitysession = new mod_visualclass_session();
    $activitysession->set_id($sessionid);
    $activitysession->read();

    // Recovering visualclass instance.
    $activityobj = new mod_visualclass_instance();
    $activityobj->set_id($activitysession->get_modid());
    $activityobj->read();

    $policygrades = (int)$activityobj->get_policygrades();

    // Updating session finalgrade and time.
    $useranswers = array();
    $sessionitems = $activitysession->get_items();
    if (!empty($sessionitems)) {
        $errors = 0;
        $correct = 0;
        foreach ($sessionitems as $sessionitem) {
            $sessionitem->is_correct() ? $correct++ : $errors++;
            $useranswers[$sessionitem->get_question()] = $sessionitem->get_answeruser_name();
        }
        $avg = $errors + $correct;
        if ($avg > 0) {
            $finalscore = round(($correct * 100) / $avg);
        }
    }

    $activitysession->set_timestop(time());
    $activitysession->set_totalscore($finalscore);
    $activitysession->write_totalscore($policygrades);
    $activitysession->write();

    // Returning response.
    $urlredirect = null;
    if ($activityobj->get_policyview() == $activityobj::VIEW_NEWTAB) {
        $urlredirect = $CFG->wwwroot . '/course/view.php?id=' . $activityobj->get_course();
    }

    if (isset($errors) && isset($correct)) {
        $response = array(
            'message' => get_string('status_sessionok', 'visualclass'),
            'buttontext' => get_string('status_buttonok', 'visualclass'),
            'labelcorrect' => get_string('status_labelcorrect', 'visualclass'),
            'labelwrong' => get_string('status_labelwrong', 'visualclass'),
            'labelscore' => get_string('status_labelscore', 'visualclass'),
            'labelquestion' => get_string('status_labelquestion', 'visualclass'),
            'labelanswer' => get_string('status_labelanswer', 'visualclass'),
            'urlredirect' => $urlredirect,
            'errors' => $errors,
            'correct' => $correct,
            'finalscore' => $finalscore,
            'hidegrade' => $activityobj->get_hide_grade(),
            'answers' => $useranswers
        );
    } else {
        $response = array(
            'message' => get_string('status_sessionok', 'visualclass'),
            'buttontext' => get_string('status_buttonok', 'visualclass'),
            'labelcorrect' => get_string('status_labelcorrect', 'visualclass'),
            'labelwrong' => get_string('status_labelwrong', 'visualclass'),
            'labelscore' => get_string('status_labelscore', 'visualclass'),
            'labelquestion' => get_string('status_labelquestion', 'visualclass'),
            'labelanswer' => get_string('status_labelanswer', 'visualclass'),
            'urlredirect' => $urlredirect
        );
    }
} else {
    $response = array(
        'message' => get_string('status_sessionadmin', 'visualclass'),
        'buttontext' => get_string('status_buttonok', 'visualclass'),
        'labelcorrect' => get_string('status_labelcorrect', 'visualclass'),
        'labelwrong' => get_string('status_labelwrong', 'visualclass'),
        'labelscore' => get_string('status_labelscore', 'visualclass'),
        'labelquestion' => get_string('status_labelquestion', 'visualclass'),
        'labelanswer' => get_string('status_labelanswer', 'visualclass'),
        'urlredirect' => $CFG->wwwroot
    );
}
echo json_encode($response);