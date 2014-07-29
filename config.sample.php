<?php
$config = array();

// DSN sent to MDB2
$config['db'] = 'mysqli://user:password@localhost/database';

// IPB posting credentials.
$config['ipbUser'] = 'botname';
$config['ipbPass'] = 'botpass';
$config['ipbURL']  = 'http://spiderwebforums.ipbhost.com/index.php?';

// Toggles a couple debugging features
$config['debug'] = false;

// Constants below here. Probably not wise to mess with them
define('B', 5);
define('G', 4);
define('A', 3);
define('S', 2);
define('P', 1);