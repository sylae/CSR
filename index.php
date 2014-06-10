<?php

/**
 * Class inclusions and things to do!
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'config.php';
require 'qp.php';
require 'HTTP/Request2.php';
require 'MDB2.php';

require 'CSR.php';
require 'scenario.php';
require 'scenPoll.php';
require 'IPB.php';

// Currently a sandbox for testing.
// TODO: Iterate over things, or something.
$scen = 12307;
$poll = new scenPoll($scen);
$csr = new scenario($scen);
$csr->updateOP();
