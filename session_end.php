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
    $session_id = $_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id];
    unset($_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id]);

    // Recovering visualclass session
    $visualclass_session = new mod_visualclass_session();
    $visualclass_session->set_id($session_id);
    $visualclass_session->read();

    // Recovering visualclass instance
    $visualclass_instance = new mod_visualclass_instance();
    $visualclass_instance->set_id($visualclass_session->get_modid());
    $visualclass_instance->read();

    $policygrades = (int)$visualclass_instance->get_policygrades();

    // Updating session finalgrade and time
    $session_items = $visualclass_session->get_items();
    if (! empty($session_items)) {
        $errors = 0;
        $correct = 0;
        foreach ($session_items as $session_item) {
            $session_item->is_correct() ? $correct++ : $errors++;
        }
        $avg = $errors + $correct;
        if ($avg > 0) {
            $finalscore = round(($correct * 100) / $avg);
        }
    }

    $visualclass_session->set_timestop(time());
    $visualclass_session->set_totalscore($finalscore);
    $visualclass_session->write_totalscore($policygrades);
    $visualclass_session->write();

    // Returning response
    $urlredirect = null;
    if ($visualclass_instance->get_policyview() == $visualclass_instance::VIEW_NEWTAB) {
        $urlredirect = $CFG->wwwroot . '/course/view.php?id=' . $visualclass_instance->get_course();
    }

    if (isset($errors) && isset($correct)) {
        $response = array(
            'message' => get_string('status_sessionok', 'visualclass'),
            'buttontext' => get_string('status_buttonok', 'visualclass'),
            'labelcorrect' => get_string('status_labelcorrect', 'visualclass'),
            'labelwrong' => get_string('status_labelwrong', 'visualclass'),
            'labelscore' => get_string('status_labelscore', 'visualclass'),
            'urlredirect' => $urlredirect,
            'errors' => $errors,
            'correct' => $correct,
            'finalscore' => $finalscore
        );
    } else {
        $response = array(
            'message' => get_string('status_sessionok', 'visualclass'),
            'buttontext' => get_string('status_buttonok', 'visualclass'),
            'labelcorrect' => get_string('status_labelcorrect', 'visualclass'),
            'labelwrong' => get_string('status_labelwrong', 'visualclass'),
            'labelscore' => get_string('status_labelscore', 'visualclass'),
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
        'urlredirect' => $CFG->wwwroot
    );
}
echo json_encode($response);