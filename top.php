<?php

require 'config.php';
require 'functions.php';
require 'MDB2.php';

$db =& MDB2::connect($config['db']);
if (PEAR::isError($db)) {
  die($db->getMessage());
}

// Grab the data we need
$query = 'SELECT * FROM composite;';
$res =& $db->query($query);

// TODO: everything

$db->disconnect();