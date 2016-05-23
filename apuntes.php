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
 *
 * @package    local
 * @subpackage apuntes
 * @copyright  2016  Matías Queirolo (mqueirolo@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/apuntes/forms.php");

global $DB, $PAGE, $OUTPUT, $USER;

$context = context_system::instance();
$url = new moodle_url("/local/apuntes/apuntes.php");
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");

// Possible actions -> view, add. Standard is view mode
$action = optional_param("action", "view", PARAM_TEXT);
$idapunte = optional_param("idapunte", null, PARAM_INT);

require_login();
if (isguestuser()){
	die();
}

$PAGE->set_title(get_string("title", "local_apuntes"));
$PAGE->set_heading(get_string("heading", "local_apuntes"));

echo $OUTPUT->header();

/*
// Adds a record to the database
if ($action == "add"){
	$addform = new addapuntes();

	if ($addform->is_cancelled()){
		$action = "view";
	}
	else if ($creationdata = $addform->get_data()){
		$record = new stdClass();
		$record->user_id = $USER->id;
		$record->course_id = $creationdata->course_id;
		$record->fecha = time();
		$record->nombre = $creationdata->name;
		$DB->insert_record("relacion", $record);
		$action = "view";
	}
}
*/
// Lists all records in the database
if ($action == "view"){
	$sql = "SELECT r.id, r.nombre, CONCAT(u.firstname, ' ', u.lastname) AS autor, c.fullname, r.fecha
			FROM {relacion} AS r, {user} AS u, {course} AS c
			WHERE u.id = r.user_id AND r.course_id = c.id
			GROUP BY r.id";
		
	$apuntes = $DB->get_records_sql($sql, array(1));
	$apuntestable = new html_table();

	if (count($apuntes) > 0){
		$apuntestable->head = array(
				"Apuntes",
				"Autor",
				"Curso",
				"Fecha"
		);

		foreach ($apuntes as $apunte){
				
			$apuntestable->data[] = array(
					$apunte->nombre,
					$apunte->autor,
					$apunte->fullname,
					date("d-m-Y", $apunte->fecha)
			);
		}
	}

	$buttonurl = new moodle_url("/local/apuntes/misapuntes.php", array("action" => "add"));
	$toprow = array();
	$toprow[] = new tabobject(
			"Buscar Apuntes",
			new moodle_url("/local/apuntes/apuntes.php"),
			"Buscar Apuntes"
			);
	$toprow[] = new tabobject(
			"Mis Apuntes",
			new moodle_url("/local/apuntes/misapuntes.php"),
			"Mis Apuntes"
			);

}
/*
// Displays the form to add a record
if ($action == "add"){
	$addform->display();
}
*/
// Displays all the records, tabs, and options
if ($action == "view"){
	echo $OUTPUT->tabtree($toprow, "Buscar Apuntes");
	if (count($apuntes) == 0){
		echo html_writer::nonempty_tag("h4", "No existen apuntes", array("align" => "center"));
	}else{
		echo html_writer::table($apuntestable);
	}

	echo html_writer::nonempty_tag("div", $OUTPUT->single_button($buttonurl, "Agregar Apunte"), array("align" => "center"));
	
}

echo $OUTPUT->footer();