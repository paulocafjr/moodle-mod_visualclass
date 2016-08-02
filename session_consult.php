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

// Setting session id.
if (isset($_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id])) {
    $sessionid = (int)$_SESSION[mod_visualclass_instance::SESSION_PREFIX . $USER->id];

    // Fetching item info.
    $item = new stdClass();
    $item->pagetitle = (string)$_REQUEST['page'];
    $item->type = (int)$_REQUEST['type'];
    $item->question = (string)$_REQUEST['question'];
    $item->answercorrect = explode('|', $_REQUEST['correctanswer']);
    $item->answeruser = (string)$_REQUEST['useranswer'];

    // Recovering visualclass session.
    $activitysession = new mod_visualclass_session();
    $activitysession->set_id($sessionid);
    $activitysession->read();

    // Seeking for question.
    $id = null;
    $items = $activitysession->get_items();
    $encoded = array();
    if (!empty($items)) {
        foreach ($items as $item) {
            $page = utf8_encode($item->get_pagetitle());
            $type = utf8_encode($item->get_type());
            $question = utf8_encode($item->get_question());
            $correctanswer = utf8_encode($item->get_answercorrect());
            $useranswer = utf8_encode($item->get_answeruser());

            $encoded[] = array(
                "page" => $page,
                "type" => $type,
                "question" => $question,
                "correctanswer" => $correctanswer,
                "useranswer" => $useranswer
            );
        }
    }

    // Checking session time.
    $activityobj = new mod_visualclass_instance();
    $activityobj->set_id($activitysession->get_modid());
    $activityobj->read();

    $time = time() - $activitysession->get_timestart();
    if ($activityobj->get_policytime() != $activityobj::TIME_UNLIMITED
        && $activityobj->get_policytime() < $time
    ) {
        $response = array(
            'timeout' => true,
            'timeout_message' => get_string('status_timeout', 'visualclass'),
            'items' => $encoded
        );
    } else {
        $response = array(
            'timeout' => false,
            'timeout_message' => get_string('status_timeout', 'visualclass'),
            'items' => $encoded
        );
    }

    // Sending response.
    echo json_encode($response);
} else {
    $response = array(
        'timeout' => false,
        'timeout_message' => get_string('status_timeout', 'visualclass')
    );
    echo json_encode($response);
}
