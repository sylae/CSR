<?php

/**
 * Class inclusions and things to do!
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'classes.php';

// Currently a sandbox for testing.
// TODO: Iterate over things, or something.
$scen = array(15667, 15659, 15676, 15675, 15671, 14150);
foreach ($scen as $s) {
  $poll = new scenPoll($s);
}
