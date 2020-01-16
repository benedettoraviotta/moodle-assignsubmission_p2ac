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
 * Strings for component "assignsubmission_p2ac", language "it"
 *
 * @package   assignsubmission_p2ac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string["pluginname"] = "Programmazione 2 automatic corrector";
$string["p2ac"] = "Programmazione 2 automatic corrector";
$string["p2ac_submissions_fa"] = "Programmazione 2 automatic corrector consegna";
$string["p2ac_tests_fa"] = "Programmazione 2 automatic corrector file di correzione";
$string['enabled'] = "Programmazione 2 automatic corrector";
$string['enabled_help'] = "Se attivo, permette agli studenti di inviare un singolo file ZIP contenente i loro esercizi Jva che saranno corretti da un set di file di correzione forniti dall'insegnante.";
$string["setting_correction"] = "Correction files";
$string["setting_corrections_help"] = "Singolo file zip contenente i file di correzione, tramite i quali verranno corretti i file consegnati dallo studente.";
$string["wsbackend_not_set"] = "Il web service base URL per il correttore non è configurato.";
$string["unexpectederror"] = "An unexpected error occured.";
$string["badrequesterror"] = "Il server non può elaborare la richiesta. Probabilmente il file ZIP inviato è corrotto.";
$string["p2ac_submission"] = "ZIP con le classi richieste.";
$string["p2ac_submission_help"] = "Un singolo file ZIP contenente tutti i file java rilevanti per l'esercizio.";
$string["no_correctionfile_warning"] = "Submission type is \"JUnit Exercise Corrector\" but no testfiles are uploaded.";

// Admin Settings
$string["default"] = "Abilitato di default";
$string["default_help"] = "Se abilitato, questo metodo di submission sarà attivo di defuatl per tutti i nuovi assignment.";
$string["wsbackend"] = "URL di base del web service.";
$string["wsbackend_help"] = "URL di base del web service, dove verranno corretti gli esercizi degli studenti.";