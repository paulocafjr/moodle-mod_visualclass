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
 * @return bool
 */
function xmldb_visualclass_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014080601) {
        $table = new xmldb_table('visualclass');
        $fieldwidth = new xmldb_field('policyview_width');
        $fieldwidth->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'policyview');
        $fieldheight = new xmldb_field('policyview_height');
        $fieldheight->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null,
            'policyview_width');

        if (! $dbman->field_exists($table, $fieldwidth)) {
            $dbman->add_field($table, $fieldwidth);
        }

        if (! $dbman->field_exists($table, $fieldheight)) {
            $dbman->add_field($table, $fieldheight);
        }

        upgrade_mod_savepoint(true, 2014080601, 'visualclass');
    }

    if ($oldversion < 2015060300) {
        $DB->set_field_select('visualclass', 'policygrades', 4, 'policygrades = ?', array(3));
        upgrade_mod_savepoint(true, 2015060300, 'visualclass');
    }

    if ($oldversion < 2015060301) {
        $DB->set_field_select('visualclass', 'policygrades', 1, 'policygrades = ?', array(3));
        upgrade_mod_savepoint(true, 2015060301, 'visualclass');
    }

    if ($oldversion < 2015072701) {
        $table = new xmldb_table('visualclass');
        $fieldhidegrade = new xmldb_field('hidegrade');
        $fieldhidegrade->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'policyview_height');

        if (!$dbman->field_exists($table, $fieldhidegrade)) {
            $dbman->add_field($table, $fieldhidegrade);
        }

        upgrade_mod_savepoint(true, 2015072701, 'visualclass');
    }

    return true;
}
