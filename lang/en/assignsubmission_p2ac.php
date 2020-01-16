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
 * Strings for component "assignsubmission_p2ac", language "en"
 *
 * @package   assignsubmission_p2ac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string["pluginname"] = "Prog 2 Automatic Corrector";
$string["p2ac"] = "Prog 2 Automatic Corrector";
$string["p2ac_submissions_fa"] = "Prog 2 Automatic Corrector submission";
$string["p2ac_tests_fa"] = "Prog 2 Automatic Corrector correction files";
$string['enabled'] = "Prog 2 Automatic Corrector";
$string['enabled_help'] = "If enabled, students are able to upload one ZIP file containing their Java exercise which will then be corrected against a teacher provided set of correction files.";
$string["setting_correction"] = "Correction files";
$string["setting_correction_help"] = "A single ZIP file containg the correction files, the students' submissions should be tested against.";
$string["wsbackend_not_set"] = "The Prog 2 Automatic Corrector web service base URL is not configurated.";
$string["unexpectederror"] = "An unexpected error occured.";
$string["badrequesterror"] = "The server could not process the request. Probably the submitted ZIP file is corrupted.";
$string["p2ac_submission"] = "Exercise ZIP";
$string["p2ac_submission_help"] = "A single ZIP file containing all the java files required for the exercise.";
$string["no_correctionfile_warning"] = "Submission type is \"Prog 2 Automatic Corrector\" but no correction files are uploaded.";

// Admin Settings
$string["default"] = "Enable by default";
$string["default_help"] = "If enable, this submission method will be enable by default for all new assignments.";
$string["wsbackend"] = "Base URL to the web service.";
$string["wsbackend_help"] = "The base URL to the web service, where all the correction files and submission wll be sent and evaluated.";