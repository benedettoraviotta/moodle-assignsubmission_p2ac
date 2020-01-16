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
 * This file contains the definition for the library class for file submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_p2ac
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// File area for p2ac submission assignment.
define('ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION', 'submissions_p2ac');
// File area for p2ac correction to be uploaded by the teacher.
define('ASSIGNSUBMISSION_P2AC_FILEAREA_CORRECTION', 'corrections_p2ac');

/**
 * library class for p2ac submission plugin extending submission plugin base class
 *
 * @package assignsubmission_p2ac
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_p2ac extends assign_submission_plugin {

    // Database table names.
    const TABLE_ASSIGNSUBMISSION_P2AC = "assignsubmission_p2ac";
    const TABLE_P2AC_FEEDBACK = "p2ac_feedback";

    //component name for language string
    const COMPONENT_NAME = "assignsubmission_p2ac";

    /**
     * Get the name of the p2ac submission plugin
     * @return string
     */
    public function get_name() {
        return get_string("p2ac", self::COMPONENT_NAME);
    }

    /**
     * Get p2ac submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_p2ac_submission($submissionid) {
        global $DB;
        $DB->set_debug(false);
        return $DB->get_record(self::TABLE_ASSIGNSUBMISSION_P2AC, array('submission_id' => $submissionid));
    }

       /**
     * Get the default setting for p2ac submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        $name = get_string("setting_correction", self::COMPONENT_NAME);
        $fileoptions = $this->get_file_options();

        $mform->addElement("filemanager", "correctionfiles", $name, null, $fileoptions);
        $mform->addHelpButton("correctionfiles",
                              "setting_correction",
                              "assignsubmission_p2ac");
        $mform->disabledIf('correctionfiles', 'assignsubmission_p2ac_enabled', 'notchecked');
    }

     /**
     * Save the settings for p2ac submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        //save uploaded file
        if (isset($data->correctionfiles)) {
            file_save_draft_area_files($data->correctionfiles, $this->assignment->get_context()->id,
                self::COMPONENT_NAME, ASSIGNSUBMISSION_P2AC_FILEAREA_CORRECTION, 0);

            // TODO Only send file to backend if checkbox in settings is checked.
            //fs contain directory with uploaded file
            $fs = get_file_storage();

            //files has an file array, with all files in fs
            $files = $fs->get_area_files($this->assignment->get_context()->id,
                self::COMPONENT_NAME,
                ASSIGNSUBMISSION_P2AC_FILEAREA_CORRECTION,
                0,
                'id',
                false);

            if (empty($files)) {
                \core\notification::warning(get_string("no_correctionfile_warning", self::COMPONENT_NAME));
                return true;
            }

            $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbackend");
            if (empty($wsbaseaddress)) {
                \core\notification::error(get_string("wsbackend_not_set", self::COMPONENT_NAME));
                return true;
            }

            $file = reset($files);
            $url = $wsbaseaddress . "/upload/teacher";
            $this->p2ac_post_teacher_file($file, $url, "file");
        }

        return true;
    }

     /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        $draftitemid = file_get_submitted_draft_itemid('correctionfiles');
        file_prepare_draft_area($draftitemid,
                                $this->assignment->get_context()->id,
                                self::COMPONENT_NAME, 
                                ASSIGNSUBMISSION_P2AC_FILEAREA_CORRECTION,
                                0, 
                                array('subdirs' => 0)
                            );
        $defaultvalues['correctionfiles'] = $draftitemid;

        return;
    }

      /**
     * File format options, allows to upload only one zip file
     *
     * @see https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms#filemanager
     *
     * @return array
     */
    private function get_file_options() {
        $fileoptions = array('subdirs' => 1,
            "maxfiles" => 1,
            'accepted_types' => array(".zip"),
            'return_types' => FILE_INTERNAL);
        return $fileoptions;
    }

     /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements_for_user($submissionorgrade, MoodleQuickForm $mform, stdClass $data, $userid) {

        $fileoptions = $this->get_file_options();
        $submissionid = $submissionorgrade ? $submissionorgrade->id : 0;

        $data = file_prepare_standard_filemanager($data,
            'consegna',
            $fileoptions,
            $this->assignment->get_context(),
            self::COMPONENT_NAME,
            ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
            $submissionid);

        $name = get_string("p2ac_submission", self::COMPONENT_NAME);
        $mform->addElement('filemanager', 'consegna_filemanager', $name, null, $fileoptions);
        $mform->addHelpButton("consegna_filemanager",
            "p2ac_submission",
            self::COMPONENT_NAME);

        return true;
    }

/**
     * Save data to the database
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $DB;
        $DB->set_debug(false);
        global $USER;

        $fileoptions = $this->get_file_options();

        $data = file_postupdate_standard_filemanager($data,
            'consegna',
            $fileoptions,
            $this->assignment->get_context(),
            self::COMPONENT_NAME,
            ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
            $submission->id);

        $fs = get_file_storage();

        if ($this->is_empty($submission)) {
            return true;
        }

        $files = $fs->get_area_files($this->assignment->get_context()->id,
            self::COMPONENT_NAME,
            ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
            $submission->id,
            'id',
            false);

        $p2acsubmission = $this->get_p2ac_submission($submission->id);
       
        if ($p2acsubmission) {
            // If there are old results, delete them. (when edit submission)
            $this->delete_feedback_data($p2acsubmission->id);
        } else {
            $p2acsubmission = new stdClass();
            $p2acsubmission->submission_id = $submission->id;
            $p2acsubmission->assignment_id = $this->assignment->get_instance()->id;
            $p2acsubmission->id = $DB->insert_record(self::TABLE_ASSIGNSUBMISSION_P2AC, $p2acsubmission);
        }

        $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbackend");
        if (empty($wsbaseaddress)) {
            \core\notification::error(get_string("wsbackend_not_set", self::COMPONENT_NAME));
            return true;
        }

        // Get the file and post it to our backend.
        $file = reset($files);
        $url = $wsbaseaddress . "/upload/student";
        //response is json object (eg. {checkerResponse:"feedback message"})
        $response = $this->p2ac_post_student_file($file, $url, "file",$USER->id);

        if (empty($response)) {
            return true;
        }
        //feedback is a stdClass, field checkerResponse:"feedback" 
        $feedback = json_decode($response);
        //represents the table p2ac_feedback (field id, message, p2ac_id(fk))
        $p2ac_feedback = new stdClass();
        //insert message from json object to p2ac table
        $p2ac_feedback->message = $feedback->checkerResponse; 
        $p2ac_feedback->p2ac_id = $p2acsubmission->id; 
        //insert record return unique id of new created record
        $p2ac_feedback->id = $DB->insert_record(self::TABLE_P2AC_FEEDBACK, $p2ac_feedback);

        return true;
        
    }   

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user record - unused
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
            self::COMPONENT_NAME,
            ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
            $submission->id,
            'timemodified',
            false);

        foreach ($files as $file) {
            // Do we return the full folder path or just the file name?
            if (isset($submission->exportfullpath) && $submission->exportfullpath == false) {
                $result[$file->get_filename()] = $file;
            } else {
                $result[$file->get_filepath().$file->get_filename()] = $file;
            }
        }
        return $result;
    }

    /**
     * Posts the teacher file to the url under the given param name.
     *
     * @param stored_file $file the file to post.
     * @param string $url the url to post to.
     * @param string $paramname the param name for the file.
     * @return mixed
     */
    private function p2ac_post_teacher_file($file, $url, $paramname) {
        if (!isset($file) or !isset($url) or !isset($paramname)) {
            return false;
        }

        $params = array(
            $paramname     => $file,
            "assignmentID" => $this->assignment->get_instance()->id
        );
        $options = array(
            "CURLOPT_RETURNTRANSFER" => true
        );
        $curl = new curl();
        $response = $curl->post($url, $params, $options);

        $info = $curl->get_info();
        if ($info["http_code"] == 200) {
            return $response;
        }

        // Something went wrong.
        debugging("P2AC: Errore durante l'invio del file al server: http_code=" . $info["http_code"]);

        if ($info['http_code'] == 400) {
            \core\notification::error(get_string("badrequesterror", self::COMPONENT_NAME));
            return false;
        } else {
            \core\notification::error(get_string("unexpectederror", self::COMPONENT_NAME));
            return false;
        }
    }

        /**
     * Posts the student file to the url under the given param name.
     *
     * @param stored_file $file the file to post.
     * @param string $url the url to post to.
     * @param string $paramname the param name for the file.
     * @param string $studentID unique studentID
     * @return mixed
     */
    private function p2ac_post_student_file($file, $url, $paramname, $studentID) {
        if (!isset($file) or !isset($url) or !isset($paramname)) {
            return false;
        }

        $params = array(
            $paramname     => $file,
            "assignmentID" => $this->assignment->get_instance()->id,
            "studentID" => $studentID
        );
        $options = array(
            "CURLOPT_RETURNTRANSFER" => true
        );
        $curl = new curl();
        $response = $curl->post($url, $params, $options);

        $info = $curl->get_info();
        if ($info["http_code"] == 200) {
            return $response;
        }

        // Something went wrong.
        debugging("P2AC: Errore durante l'invio del file al server: http_code=" . $info["http_code"]);

        if ($info['http_code'] == 400) {
            \core\notification::error(get_string("badrequesterror", self::COMPONENT_NAME));
            return false;
        } else {
            \core\notification::error(get_string("unexpectederror", self::COMPONENT_NAME));
            return false;
        }
    }

    /**
     * The assignment has been deleted - cleanup db
     * When edit submission
     * Also when course has been deleted
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->set_debug(false);
           
        $assignmentid = $this->assignment->get_instance()->id;

        $p2ac = $DB->get_record(self::TABLE_ASSIGNSUBMISSION_P2AC, array('assignment_id' => $assignmentid), "id");
        if ($p2ac) {
            $this->delete_feedback_data($p2ac->id);
        }

        // Delete p2ac assignment.
        $DB->delete_records(self::TABLE_ASSIGNSUBMISSION_P2AC, array("assignment_id" => $assignmentid));

        //delete file 
        return $this->delete_backend_files($assignmentid);

    }

    /**
     *  Delete files from server when assignment has been deleted
     * @return bool
     */

    private function delete_backend_files($assignmentid){

        $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbackend");
        if (empty($wsbaseaddress)) {
            \core\notification::error(get_string("wsbackend_not_set", self::COMPONENT_NAME));
            return true;
        }

        $url = $wsbaseaddress . "/delete/teacher?assignmentID=" . $assignmentid;
        $curl = new curl();
        $curl->delete($url);
        
        return true;
    }

     /**
     * Remove files from this submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;
        $fs = get_file_storage();

        $fs->delete_area_files($this->assignment->get_context()->id,
                               self::COMPONENT_NAME,
                               ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
                               $submission->id);

        $currentsubmission = $this->get_p2ac_submission($submission->id);
        $currentsubmission->message = "";
        $DB->update_record(self::TABLE_P2AC_FEEDBACK, $currentsubmission);

        return true;
    }


    private function delete_feedback_data($p2acid) {
        global $DB;
        $DB->set_debug(false);

        $feedback = $DB->get_record(self::TABLE_P2AC_FEEDBACK, array("p2ac_id" => $p2acid), "id", IGNORE_MISSING);
        if (!$feedback) {
            return true;
        }

        // Delete checker feedback.
        $DB->delete_records(self::TABLE_P2AC_FEEDBACK, array("p2ac_id" => $p2acid));

        return true;
    }

    /**
     * Return true if there are no submission files
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION) == 0;
    }

    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
            self::COMPONENT_NAME,
            $area,
            $submissionid,
            'id',
            false);

        return count($files);
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(
            ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION => get_string("p2ac_submissions_fa", self::COMPONENT_NAME),
            ASSIGNSUBMISSION_P2AC_FILEAREA_CORRECTION => get_string("p2ac_tests_fa", self::COMPONENT_NAME)
        );
    }


    //da qui in poi è tutta visualizzazione dati

    /** 
     * Display the test results of the submission.
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
   */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $PAGE;

        if ($PAGE->url->get_param("action") == "grading") {
            return $this->view_grading_summary($submission, $showviewlink);
        } else {
            return $this->view_student_summary($submission);
        }
    }

    /**
     * Returns the view that should be displayed in the grading table.
     *
     * @param stdClass $submission
     * @param bool $showviewlink
     * @return string
     */
    private function view_grading_summary(stdClass $submission, & $showviewlink) {
        global $DB;
        $DB->set_debug(false);
        $showviewlink = true;

        $p2acsubmission = $DB->get_record(self::TABLE_ASSIGNSUBMISSION_P2AC, array("submission_id" => $submission->id));
        $feedback = $DB->get_record(self::TABLE_P2AC_FEEDBACK, array("p2ac_id" => $p2acsubmission->id));
        $message = $feedback->message;
        $message = html_writer::div($message, "message");

        return $message;
    }

    //visualizzazione risultati studente//

    /**
     * Returns the view that should be displayed to the student.
     *
     * @param stdClass $submission
     * @return string
     */
    private function view_student_summary(stdClass $submission) {
        return $this->view($submission);
    }
    /**
     * Shows the test results of the submission.
     *
     * @param stdClass $submission the submission the results are shown for.
     * @return string the view of the test results as html.
     */
    public function view(stdClass $submission) {
        global $DB;
        $DB->set_debug(false);
        $html = "";

        $html .= $this->assignment->render_area_files(self::COMPONENT_NAME,
                                                      ASSIGNSUBMISSION_P2AC_FILEAREA_SUBMISSION,
                                                      $submission->id);
        //uso get_record per entrambi perchè mi interessa un singolo field della tabella, altrimenti usavo get_records
        $p2acsubmission = $DB->get_record(self::TABLE_ASSIGNSUBMISSION_P2AC, array("submission_id" => $submission->id));
        $checker_feedback = $DB->get_record(self::TABLE_P2AC_FEEDBACK, array("p2ac_id" => $p2acsubmission->id)); 
        
        $html .=html_writer::start_tag("br");
        $html .=html_writer::start_tag("br");
        $html .=html_writer::tag("h4", "Feedback about your submission:");
        $html .=html_writer::start_tag("br");
        $html .=html_writer::end_tag("br");
        if(empty($checker_feedback->message))
            {
                $html .=html_writer::div("Nessun feedback.");    
            }
        else{
                //far andare css!!
                $html .=html_writer::div($checker_feedback->message , 'message');   
            }  

        $html = html_writer::div($html);                                        
        return $html; 
                                             
            
    }  
        
}    