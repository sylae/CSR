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
require 'Auth/Auth.php';
require 'Auth/Container.php';
require 'Auth/Container/MDB2.php';
require 'Auth/Container/SyAuth.php';

// Local libraries
require 'libs/misc.php';

// Local classes
require 'classes/CSR.php';
require 'classes/scenario.php';
require 'classes/scenPoll.php';
require 'classes/IPB.php';
require 'classes/webuser.php';
require 'classes/webpage.php';
require 'classes/webpage_front.php';
require 'classes/webpage_scenario.php';

// queries
require 'classes/queryCSR.php';
require 'classes/queryUnranked.php';