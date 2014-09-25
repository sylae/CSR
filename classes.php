<?php

/**
 * Master class inclusions file
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

// Global Config
require 'config.php';

// Libraries
require 'qp.php'; // don't fall for that 2.x crap.
require 'HTTP/Request2.php';
require 'MDB2.php';

// Local classes
require 'classes/CSR.php';
require 'classes/scenario.php';
require 'classes/scenPoll.php';
require 'classes/IPB.php';

// queries
require 'classes/queryCSR.php';
require 'classes/queryUnranked.php';