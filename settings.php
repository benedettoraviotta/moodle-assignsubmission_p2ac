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
 * This file defines the admin settings for this plugin
 *
 * @package   assignsubmission_p2ac
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Note: This is on by default.
//keep default settings and add web service setting
$settings->add(new admin_setting_configcheckbox('assignsubmission_p2ac/default',
                   new lang_string('default', 'assignsubmission_p2ac'),
                   new lang_string('default_help', 'assignsubmission_p2ac'), 1));

$settings->add(new admin_setting_configtext('assignsubmission_p2ac/wsbackend',
                   new lang_string('wsbackend', 'assignsubmission_p2ac'),
                   new lang_string('wsbackend_help', 'assignsubmission_p2ac'), 1));


