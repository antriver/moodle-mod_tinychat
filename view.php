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
 * Displays a single tinychat instance
 *
 * @package    mod_tinychat
 * @copyright  2014 Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$t  = optional_param('t', 0, PARAM_INT);  // tinychat instance ID

if ($id) {
    $cm = get_coursemodule_from_id('tinychat', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tinychat  = $DB->get_record('tinychat', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($t) {
    $tinychat  = $DB->get_record('tinychat', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $tinychat->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('tinychat', $tinychat->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

require_capability('mod/tinychat:view', $context);

add_to_log($course->id, 'tinychat', 'view', "view.php?id={$cm->id}", $tinychat->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/tinychat/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tinychat->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

// If we're displaying the intro
if ($tinychat->intro) {
    echo $OUTPUT->box(format_module_intro('tinychat', $tinychat, $cm->id), 'generalbox mod_introbox', 'tinychatintro');
}

$tinyChatParams = array(
  'room' => $tinychat->chaturl, //Name of the chat to join (tinychat.com/this)
  'join' => 'auto', //Automatically join the room
  'nick' => $USER->username, //Nickname to use in the room
);

// Load the tinychat.com JS
echo '<script type="text/javascript">';
echo 'var tinychat = ' . json_encode($tinyChatParams) .';';
echo '</script>';
echo '<script src="http://tinychat.com/js/embed.js"></script>';

echo '<style type="text/css">';
echo '.tinychat_embed {
  height:500px !important;
}
.tinychat_attribution {
  text-align:center;
  font-size:11px;
  margin-top:5px;
}
';
echo '</style>';

echo '<div id="client" class="tinychat_attribution">';
echo '<a href="http://tinychat.com">Video chat</a> provided by Tinychat.com';
echo '</div>';

// Finish the page
echo $OUTPUT->footer();
