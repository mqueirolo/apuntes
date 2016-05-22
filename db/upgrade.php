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
 * This file keeps track of upgrades to the evaluaciones block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.0
 * @package blocks
 * @copyright 2016 Matías Queirolo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */


function xmldb_local_apuntes_upgrade($oldversion) {
	global $CFG, $DB;

	$dbman = $DB->get_manager();
	
	if ($oldversion < 2016051901) {
	
		// Define table apuntes to be created.
		$table = new xmldb_table('apuntes');
	
		// Adding fields to table apuntes.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('nombre', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('fechasubida', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('relacion_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
	
		// Adding keys to table apuntes.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('relacion_id', XMLDB_KEY_FOREIGN, array('relacion_id'), 'relacion', array('id'));
	
		// Conditionally launch create table for apuntes.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Apuntes savepoint reached.
		upgrade_plugin_savepoint(true, 2016051901, 'local', 'apuntes');
	}
	
	if ($oldversion < 2016051901) {
	
		// Define table relacion to be created.
		$table = new xmldb_table('relacion');
	
		// Adding fields to table relacion.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('course_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
	
		// Adding keys to table relacion.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));
		$table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
	
		// Conditionally launch create table for relacion.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Apuntes savepoint reached.
		upgrade_plugin_savepoint(true, 2016051901, 'local', 'apuntes');
	}
	
	if ($oldversion < 2016052101) {
	
		// Define field nombre to be added to relacion.
		$table = new xmldb_table('relacion');
		$field = new xmldb_field('nombre', XMLDB_TYPE_TEXT, null, null, null, null, null, 'course_id');
	
		// Conditionally launch add field nombre.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Apuntes savepoint reached.
		upgrade_plugin_savepoint(true, 2016052101, 'local', 'apuntes');
	}
	
	if ($oldversion < 2016052101) {
	
		// Define field fecha to be added to relacion.
		$table = new xmldb_table('relacion');
		$field = new xmldb_field('fecha', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'nombre');
	
		// Conditionally launch add field fecha.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Apuntes savepoint reached.
		upgrade_plugin_savepoint(true, 2016052101, 'local', 'apuntes');
	}
	

	
    
	return true;
}