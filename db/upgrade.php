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
 * Upgrade procedures for mod_visualclass
 *
 * @package    mod
 * @subpackage visualclass
 * @copyright  2013 Caltech Informática Ltda <class@class.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute visualclass upgrade from the given old version
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_visualclass_upgrade($oldversion) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2014080601) {
        $table = new xmldb_table('visualclass');
        $field_policyview_width = new xmldb_field('policyview_width');
        $field_policyview_width->set_attributes(XMLDB_TYPE_INT, '10', XMLDB_UNSIGNED, null, null, 'default null', 'policyview');
        $field_policyview_height = new xmldb_field('policyview_height');
        $field_policyview_height->set_attributes(XMLDB_TYPE_INT, '10', XMLDB_UNSIGNED, null, null, 'default null', 'policyview_width');
        
        if (! $dbman->field_exists($table, $field_policyview_width)) {
            $dbman->add_field($table, $field_policyview_width);
        }
        
        if (! $dbman->field_exists($table, $field_policyview_height)) {
            $dbman->add_field($table, $field_policyview_height);
        }
        
        upgrade_mod_savepoint(true, 2014080601, 'visualclass');
    }
    
    return true;
}
