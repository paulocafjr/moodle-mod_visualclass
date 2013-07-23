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
 * Writes a session item for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $USER;

// Setting session id
if (isset($_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id])) {
    $session_id = (int) $_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id];

    // Fetching item info
    $item = new stdClass();
    $item->pagetitle = (string) $_REQUEST['page'];
    $item->type = (int) $_REQUEST['type'];
    $item->question = (string) $_REQUEST['question'];
    $item->answercorrect = explode('|', $_REQUEST['correctanswer']);
    $item->answeruser = (string) $_REQUEST['useranswer'];

    // Recovering visualclass session
    $visualclass_session = new mod_visualclass_session();
    $visualclass_session->set_id($session_id);
    $visualclass_session->read();

    // Seeking for question
    $id = null;
    $items = $visualclass_session->get_items();
    if (! empty($items)) {
        foreach ($items as $olditem) {
            if (strcmp($olditem->get_question(), $item->question) === 0) {
                $id = $olditem->get_id();
            }
        }
    }

    // Recording up session item
    $visualclass_sessionitem = new mod_visualclass_sessionitem();
    $visualclass_sessionitem->set_id($id);
    $visualclass_sessionitem->set_sessionid($session_id);
    $visualclass_sessionitem->set_pagetitle($item->pagetitle);
    $visualclass_sessionitem->set_type($item->type);
    $visualclass_sessionitem->set_question($item->question);
    $visualclass_sessionitem->set_answercorrect($item->answercorrect);
    $visualclass_sessionitem->set_answeruser($item->answeruser);
    $visualclass_sessionitem->write();

    // Checking session time
    $visualclass_instance = new mod_visualclass_instance();
    $visualclass_instance->set_id($visualclass_session->get_modid());
    $visualclass_instance->read();

    $time = time() - $visualclass_session->get_timestart();
    if ($visualclass_instance->get_policytime() != $visualclass_instance::TIME_UNLIMITED &&
        $visualclass_instance->get_policytime() < $time) {
        $response = array(
            'timeout' => true,
            'timeout_message' => get_string('status_timeout', 'visualclass')
        );
    } else {
        $response = array(
            'timeout' => false,
            'timeout_message' => get_string('status_timeout', 'visualclass')
        );
    }

    // Sending response
    echo json_encode($response);
} else {
    $response = array(
        'timeout' => false,
        'timeout_message' => get_string('status_timeout', 'visualclass')
    );
    echo json_encode($response);
}