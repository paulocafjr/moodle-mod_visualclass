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
 * This script produces mod reports as spreadsheet.
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2015 Caltech Informática {@link http://class.com.br}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/../../user/lib.php');
require_once($CFG->libdir . '/excellib.class.php');
require_once($CFG->dirroot.'/mod/visualclass/lib.php');

$context = context_system::instance();
$PAGE->set_url('/mod/visualclass/export_xlsx.php');
$PAGE->set_context($context);

require_login();
require_capability('mod/visualclass:reports', $context);

// Retrieving given parameters
$cmid = required_param('cmid', PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHANUM);

// Static parameters
$courseid = 0;
$sectionnum = false;
$strictness = MUST_EXIST;

// Retrieving module information
$cm = get_coursemodule_from_id('visualclass', $cmid, $courseid, $sectionnum, $strictness);
$course = $DB->get_record('course', array('id' => $cm->course), '*', $strictness);
$module = $DB->get_record('visualclass', array('id' => $cm->instance), '*', $strictness);

// New module instance
$instance = new mod_visualclass_instance();
$instance->set_id($module->id);
$instance->read();

// Get valid sessions
$allsessions = $instance->get_sessions();
$validsessions = array();
foreach ($allsessions as $session) {
    if ($session->get_timestop()) {
        $validsessions[] = $session;
    }
}

// New Excel workbook and worksheet instance
$currenttime = time();
$extension = 'xlsx';
$filename = $currenttime . $extension;
$workbook = new MoodleExcelWorkbook($filename, 'Excel2007');
$worksheet = $workbook->add_worksheet('Moodle');

// Writing report
$rowindex = 0;
$columnindex = 0;
if ($type === mod_visualclass_instance::REPORT_USER) {
    // Header
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_name', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_attempt', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percentcorrect', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percentwrong', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_totalscore', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_time', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_pagetitle', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_type', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_question', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_answercorrect', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_answeruser', 'visualclass'));
    
    // Rows
    foreach ($validsessions as $session) {
        // Get user full name
        $userid = $session->get_userid();
        $users = user_get_users_by_id(array($userid));
        $user = $users[$userid];
        $fullname = $user->firstname . ' ' . $user->lastname;
        
        $sessionitems = $session->get_items();
        sort($sessionitems);
        if (!empty($sessionitems)) {
            foreach ($sessionitems as $item) {
                $rowindex++;
                $columnindex = 0;

                // User full name
                $worksheet->write($rowindex, $columnindex++, $fullname);

                // User attempt number
                $worksheet->write_number($rowindex, $columnindex++, $session->get_attemptnumber());
                
                // User correct answers
                $worksheet->write_number($rowindex, $columnindex++, $session->get_correct_answers());
                
                // User wrong answers
                $worksheet->write_number($rowindex, $columnindex++, $session->get_wrong_answers());
                
                // User grade
                $worksheet->write_number($rowindex, $columnindex++, $session->get_totalscore());
                
                // User time
                $worksheet->write_number($rowindex, $columnindex++, $session->get_time());

                // Page title
                $worksheet->write($rowindex, $columnindex++, $item->get_pagetitle());

                // Question type
                $worksheet->write($rowindex, $columnindex++, $item->get_type_name());

                // Question
                $worksheet->write($rowindex, $columnindex++, $item->get_question());

                // Correct Answer
                $worksheet->write($rowindex, $columnindex++, $item->get_answercorrect_name());

                // User Answer
                $worksheet->write($rowindex, $columnindex++, $item->get_answeruser_name());
                
                // Either correct or not
                if ($item->is_correct()) {
                    $worksheet->write($rowindex, $columnindex++, $item->is_correct_name(), $workbook->add_format(array('bg_color'=>'green')));
                } else {
                    $worksheet->write($rowindex, $columnindex++, $item->is_correct_name(), $workbook->add_format(array('bg_color'=>'red')));
                }
            }
            
            // Extra line
            $worksheet->write(++$rowindex, 0, '');
        }
    }
} else if ($type === mod_visualclass_instance::REPORT_QUESTION) {
    // Header
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_question', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percentcorrect', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percentwrong', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percenttotal', 'visualclass'));
    $worksheet->write($rowindex, $columnindex++, get_string('xlsx_percent', 'visualclass'));
    
    // Math
    $percentage = array();
    foreach ($validsessions as $session) {
        $sessionitems = $session->get_items();
        sort($sessionitems);
        if (!empty($sessionitems)) {
            foreach ($sessionitems as $item) {
                if (!isset($percentage[$item->get_question()])) {
                    $percentage[$item->get_question()] = array(
                        'count' => 1,
                        'correct' => $item->is_correct() ? 1 : 0,
                        'wrong' => $item->is_correct() ? 0 : 1
                    );
                } else {
                    $percentage[$item->get_question()]['count'] += 1;
                    $percentage[$item->get_question()]['correct'] += $item->is_correct() ? 1 : 0;
                    $percentage[$item->get_question()]['wrong'] += $item->is_correct() ? 0 : 1;
                }
            }
        }
    }
    
    // Rows
    foreach ($percentage as $question => $data) {
        $rowindex++;
        $columnindex = 0;

        // Question
        $worksheet->write($rowindex, $columnindex++, $question);

        // Correct Answers
        $worksheet->write_number($rowindex, $columnindex++, $data['correct']);

        // Wrong Answers
        $worksheet->write_number($rowindex, $columnindex++, $data['wrong']);

        // Total
        $worksheet->write_number($rowindex, $columnindex++, $data['count']);

        // Percentage of correct answers
        $worksheet->write_number($rowindex, $columnindex++, round($data['correct'] * 100 / $data['count']));
    }
}

// Finish workbook
$workbook->close();
die;