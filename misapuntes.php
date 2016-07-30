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


//cambiar idattendance por idalumno, hacer lo mismo con apuntes.php
require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/apuntes/forms.php");

global $DB, $PAGE, $OUTPUT, $USER;

$context = context_system::instance();
$url = new moodle_url("/local/apuntes/misapuntes.php");
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");

// Possible actions -> view, add, edit or delete. Standard is view mode
$action = optional_param("action", "view", PARAM_TEXT);
$idattendance = optional_param("idapunte", null, PARAM_INT);

require_login();
if (isguestuser()){
	die();
}

$PAGE->set_title(get_string("title", "local_apuntes"));
$PAGE->set_heading(get_string("heading", "local_apuntes"));

echo $OUTPUT->header();

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

// Edits an existent record
if($action == "edit"){
	if($idattendance == null){
		print_error("Apunte no seleccionado");
		$action = "view";
	}
	else{
		if($attendance = $DB->get_record("relacion", array("id" => $idattendance))){
			$editform = new editapuntes(null, array(
					"idapunte" => $idattendance
			));
				
			$defaultdata = new stdClass();
			$defaultdata->course_id = $attendance->course_id;
			$defaultdata->name = $attendance->nombre;
			$editform->set_data($defaultdata);
				
			if($editform->is_cancelled()){
				$action = "view";
				
			}
			else if($editform->get_data()){
				
				$record = new stdClass();
				$record->id = $idattendance;
				$record->user_id = $USER->id;
				$record->course_id = $editform->get_data()->course_id;
				$record->fecha = time();
				$record->nombre = $editform->get_data()->name;
				$DB->update_record("relacion", $record);
				$action = "view";
			}
		}
		else{
			print_error("Apunte no existe");
			$action = "view";
		}
	}
}

// Delete the selected record
if ($action == "delete"){
	if ($idattendance == null){
		print_error("Apunte no seleccionado");
		$action = "view";
	}else{
		if ($attendance = $DB->get_record("relacion", array("id" => $idattendance))){
			$DB->delete_records("relacion", array("id" => $attendance->id));
			$action = "view";
		}else{
			print_error("Apunte no existe");
			$action = "view";
		}
	}
}

// Lists all records in the database
if ($action == "view"){
	$sql = "SELECT r.id, r.nombre, CONCAT(u.firstname, ' ', u.lastname) AS autor, c.fullname, r.fecha, r.user_id
			FROM {relacion} AS r, {user} AS u, {course} AS c
			WHERE u.id = r.user_id AND r.course_id = c.id
			GROUP BY r.id
			HAVING r.user_id = $USER->id"; 
			

	$attendances = $DB->get_records_sql($sql, array(1));
	$attendancestable = new html_table();

	if (count($attendances) > 0){
		$attendancestable->head = array(
				"Mis Apuntes",
				"Autor",
				"Curso",
				"Fecha",
				"Ajustes"
		);

		foreach ($attendances as $attendance){
			// Define delete icon and url
			$deleteurl_apunte = new moodle_url("/local/apuntes/misapuntes.php", array(
					"action" => "delete",
					"idapunte" => $attendance->id,
			));
			$deleteicon_apunte = new pix_icon("t/delete", "Borrar");
			$deleteaction_apunte = $OUTPUT->action_icon(
					$deleteurl_apunte,
					$deleteicon_apunte,
					new confirm_action("Confirme")
					);

			// Define edition icon and url
			$editurl_apunte = new moodle_url("/local/apuntes/misapuntes.php", array(
					"action" => "edit",
					"idapunte" => $attendance->id
			));
			$editicon_apunte = new pix_icon("i/edit", "Editar");
			$editaction_apunte = $OUTPUT->action_icon(
					$editurl_apunte,
					$editicon_apunte,
					new confirm_action("Confirme")
					);

			$attendancestable->data[] = array(
					$attendance->nombre,
					$attendance->autor,
					$attendance->fullname,
					date("d-m-Y", $attendance->fecha),
					$deleteaction_apunte.$editaction_apunte
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

// Displays the form to add a record
if ($action == "add"){
	$addform->display();
}

// Displays the form to edit a record
if( $action == "edit" ){
	$editform->display();
}

// Displays all the records, tabs, and options
if ($action == "view"){
	
	echo $OUTPUT->tabtree($toprow, "Mis Apuntes");
	if (count($attendances) == 0){
		echo html_writer::nonempty_tag("h4", "No existen apuntes", array("align" => "center"));
	}else{
		echo html_writer::table($attendancestable);
	}

	echo html_writer::nonempty_tag("div", $OUTPUT->single_button($buttonurl, "Agregar Apunte"), array("align" => "center"));
}

echo $OUTPUT->footer();






