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
 * Main configuration form for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Editing Form
 *
 * @package    mod_visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_visualclass_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        // General Header

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name Field

        $mform->addElement(
            'text', 'name',
            get_string('felem_name', 'visualclass'),
            array('size' => '128')
        );
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule(
            'name', get_string('maximumchars', '', 128),
            'maxlength', 128, 'client'
        );
        $mform->addHelpButton('name', 'felem_name', 'visualclass');

        // File Field
        if (empty($this->current->instance)) {
            $fileoptions = array(
                'maxbytes' => 0,
                'accepted_types' => '.zip'
            );
            $mform->addElement(
                'filepicker', 'file',
                get_string('felem_file', 'visualclass'), null,
                $fileoptions
            );
            $mform->addRule('file', null, 'required', null, 'client');
            $mform->addHelpButton('file', 'felem_file', 'visualclass');
        }

        // Settings Header

        $mform->addElement('header', 'settings', get_string('felem_header_settings', 'visualclass'));

        // Project Subject Field
        // Automatically fetch page subject instead

        /*$mform->addElement('text', 'projectsubject',
            get_string('felem_projectsubject', 'visualclass'),
            array('size'=>'128'));
        if (! empty($CFG->formatstringstriptags)) {
            $mform->setType('projectsubject', PARAM_TEXT);
        } else {
            $mform->setType('projectsubject', PARAM_CLEAN);
        }
        $mform->addRule('projectsubject', null, 'required', null, 'client');
        $mform->addRule('projectsubject', get_string('maximumchars', '', 128),
            'maxlength', 128, 'client');*/

        // Attempts Field

        $unlimited = get_string('felem_attempts_unlimited', 'visualclass');
        $attemptsoptions = array(
            mod_visualclass_instance::ATTEMPT_UNLIMITED => $unlimited
        );
        for ($i = 1; $i <= mod_visualclass_instance::ATTEMPT_MAX; $i++) {
            $attemptsoptions[$i] = (string)$i;
        }
        $mform->addElement(
            'select', 'policyattempts',
            get_string('felem_attempts', 'visualclass'),
            $attemptsoptions, null
        );

        // Grading Field

        $average = get_string('felem_grades_average', 'visualclass');
        $best = get_string('felem_grades_best', 'visualclass');
        $worst = get_string('felem_grades_worst', 'visualclass');
        $gradesoptions = array(
            mod_visualclass_instance::GRADE_BEST => $best,
            mod_visualclass_instance::GRADE_AVERAGE => $average,
            mod_visualclass_instance::GRADE_WORST => $worst
        );
        $mform->addElement(
            'select', 'policygrades',
            get_string('felem_grades', 'visualclass'),
            $gradesoptions, null
        );

        // Time Field

        /*$unit = get_string('felem_time_unit', 'visualclass');
        $unlimited = get_string('felem_time_unlimited', 'visualclass');
        $timeoptions = array(
            mod_visualclass_instance::TIME_UNLIMITED => $unlimited
        );
        $factor = mod_visualclass_instance::TIME_FACTOR;
        while ($factor <= mod_visualclass_instance::TIME_MAX) {
            $timeoptions[$factor] = (string) $factor / mod_visualclass_instance::TIME_BASE
                . ' ' . $unit;
            $factor += mod_visualclass_instance::TIME_FACTOR;
        }
        $mform->addElement('select', 'policytime',
            get_string('felem_time', 'visualclass'),
            $timeoptions, null);*/

        // View Field

        $moodle = get_string('felem_view_moodle', 'visualclass');
        $newtab = get_string('felem_view_newtab', 'visualclass');
        $popup = get_string('felem_view_popup', 'visualclass');
        $viewoptions = array(
            mod_visualclass_instance::VIEW_NEWTAB => $newtab,
            mod_visualclass_instance::VIEW_MOODLE => $moodle,
            mod_visualclass_instance::VIEW_POPUP => $popup
        );
        $mform->addElement(
            'select', 'policyview',
            get_string('felem_view', 'visualclass'),
            $viewoptions, null
        );
        
        $mform->addElement(
            'text', 'policyview_width',
            get_string('felem_view_popup_width', 'visualclass'),
            array('size' => 3)
        );
        $mform->disabledIf('policyview_width', 'policyview', 'neq', mod_visualclass_instance::VIEW_POPUP);
        $mform->setType('policyview_width', PARAM_INT);
        
        $mform->addElement(
            'text', 'policyview_height',
            get_string('felem_view_popup_height', 'visualclass'),
            array('size' => 3)
        );
        $mform->disabledIf('policyview_height', 'policyview', 'neq', mod_visualclass_instance::VIEW_POPUP);
        $mform->setType('policyview_height', PARAM_INT);

        // Standard Fields

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     *
     * @return array|void
     */
    public function validation($data, $files) {
        global $CFG;

        $errors = array();
        $file = $CFG->dirroot . '/visualclass/';

        if ((file_exists($file) && !is_writable($file)) || (!file_exists($file) && !is_writable($CFG->dirroot))) {
            $errors['file'] = get_string('error_nohome', 'visualclass');
        }

        return $errors;
    }
}