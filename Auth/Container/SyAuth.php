<?php

/**
 * PBKDF2 container for PEAR Auth.
 * Adapted from @link https://defuse.ca/php-pbkdf2.htm
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 8192);
define("PBKDF2_SALT_BYTE_SIZE", 128);
define("PBKDF2_HASH_BYTE_SIZE", 256);
define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

include_once 'Auth/Auth.php';
include_once 'Auth/Container.php';
require_once 'MDB2.php';

class Auth_Container_SyAuth extends Auth_Container_MDB2 {

  function addUser($username, $password, $additional = "") {
    $this->log('Auth_Container_MDB2::addUser() called.', AUTH_LOG_DEBUG);

    // Prepare for a database query
    $err = $this->_prepare();
    if ($err !== true) {
      return PEAR::raiseError($err->getMessage(), $err->getCode());
    }

    $password = $this->create_hash($password);

    $additional_key = '';
    $additional_value = '';

    if (is_array($additional)) {
      foreach ($additional as $key => $value) {
        if ($this->options['auto_quote']) {
          $additional_key .= ', ' . $this->db->quoteIdentifier($key, true);
        } else {
          $additional_key .= ', ' . $key;
        }
        $additional_value .= ', ' . $this->db->quote($value, 'text');
      }
    }

    $query = sprintf("INSERT INTO %s (%s, %s%s) VALUES (%s, %s%s)", $this->options['final_table'], $this->options['final_usernamecol'], $this->options['final_passwordcol'], $additional_key, $this->db->quote($username, 'text'), $this->db->quote($password, 'text'), $additional_value
    );

    $this->log('Running SQL against MDB2: ' . $query, AUTH_LOG_DEBUG);

    $res = $this->query($query);

    if (MDB2::isError($res)) {
      return PEAR::raiseError($res->getMessage(), $res->code);
    }
    return true;
  }

  function supportsChallengeResponse() {
    return FALSE;
  }

  function fetchData($username, $password) {
    $this->log('Auth_Container_MDB2::fetchData() called.', AUTH_LOG_DEBUG);
    // Prepare for a database query
    $err = $this->_prepare();
    if ($err !== true) {
      return PEAR::raiseError($err->getMessage(), $err->getCode());
    }

    //Check if db_fields contains a *, if so assume all columns are selected
    if (is_string($this->options['db_fields']) && strstr($this->options['db_fields'], '*')) {
      $sql_from = '*';
    } else {
      $sql_from = $this->options['final_usernamecol'] .
        ", " . $this->options['final_passwordcol'];

      if (strlen($fields = $this->_quoteDBFields()) > 0) {
        $sql_from .= ', ' . $fields;
      }
    }
    $query = sprintf("SELECT %s FROM %s WHERE %s = %s", $sql_from, $this->options['final_table'], $this->options['final_usernamecol'], $this->db->quote($username, 'text')
    );

    // check if there is an optional parameter db_where
    if ($this->options['db_where'] != '') {
      // there is one, so add it to the query
      $query .= " AND " . $this->options['db_where'];
    }

    $this->log('Running SQL against MDB2: ' . $query, AUTH_LOG_DEBUG);

    $res = $this->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
    if (MDB2::isError($res) || PEAR::isError($res)) {
      return PEAR::raiseError($res->getMessage(), $res->getCode());
    }
    if (!is_array($res)) {
      $this->activeUser = '';
      return false;
    }

    // Perform trimming here before the hashing
    $password = trim($password, "\r\n");
    $res[$this->options['passwordcol']] = trim($res[$this->options['passwordcol']], "\r\n");

    if ($this->verifyPassword($password, $res[$this->options['passwordcol']])) {
      // Store additional field values in the session
      foreach ($res as $key => $value) {
        if ($key == $this->options['passwordcol'] ||
          $key == $this->options['usernamecol']) {
          continue;
        }

        $this->log('Storing additional field: ' . $key, AUTH_LOG_DEBUG);

        // Use reference to the auth object if exists
        // This is because the auth session variable can change so a static call to setAuthData does not make sense
        $this->_auth_obj->setAuthData($key, $value);
      }
      return true;
    }

    $this->activeUser = $res[$this->options['usernamecol']];
    return false;
  }

  function verifyPassword($password1, $password2) {

    $this->log('Auth_Container::verifyPassword() called.', AUTH_LOG_DEBUG);

    return $this->validate_password($password1, $password2);
  }

  function changePassword($username, $password) {
    $this->log('Auth_Container_MDB2::changePassword() called.', AUTH_LOG_DEBUG);
    // Prepare for a database query
    $err = $this->_prepare();
    if ($err !== true) {
      return PEAR::raiseError($err->getMessage(), $err->getCode());
    }

    $password = $this->create_hash($password);

    $query = sprintf("UPDATE %s SET %s = %s WHERE %s = %s", $this->options['final_table'], $this->options['final_passwordcol'], $this->db->quote($password, 'text'), $this->options['final_usernamecol'], $this->db->quote($username, 'text')
    );

    // check if there is an optional parameter db_where
    if ($this->options['db_where'] != '') {
      // there is one, so add it to the query
      $query .= " AND " . $this->options['db_where'];
    }

    $this->log('Running SQL against MDB2: ' . $query, AUTH_LOG_DEBUG);

    $res = $this->query($query);

    if (MDB2::isError($res)) {
      return PEAR::raiseError($res->getMessage(), $res->code);
    }
    return true;
  }

  function create_hash($password) {
    // format: algorithm:iterations:salt:hash
    $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
    return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $salt . ":" .
      base64_encode($this->pbkdf2(
          PBKDF2_HASH_ALGORITHM, $password, $salt, PBKDF2_ITERATIONS, PBKDF2_HASH_BYTE_SIZE, true
    ));
  }

  function validate_password($password, $correct_hash) {
    $params = explode(":", $correct_hash);
    if (count($params) < HASH_SECTIONS)
      return false;
    $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
    return $this->slow_equals(
        $pbkdf2, $this->pbkdf2(
          $params[HASH_ALGORITHM_INDEX], $password, $params[HASH_SALT_INDEX], (int) $params[HASH_ITERATION_INDEX], strlen($pbkdf2), true
        )
    );
  }

  function slow_equals($a, $b) {
    $diff = strlen($a) ^ strlen($b);
    for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
      $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
  }

  function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
    $algorithm = strtolower($algorithm);
    if (!in_array($algorithm, hash_algos(), true))
      die('PBKDF2 ERROR: Invalid hash algorithm.');
    if ($count <= 0 || $key_length <= 0)
      die('PBKDF2 ERROR: Invalid parameters.');

    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);

    $output = "";
    for ($i = 1; $i <= $block_count; $i++) {
      // $i encoded as 4 bytes, big endian.
      $last = $salt . pack("N", $i);
      // first iteration
      $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
      // perform the other $count - 1 iterations
      for ($j = 1; $j < $count; $j++) {
        $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
      }
      $output .= $xorsum;
    }

    if ($raw_output)
      return substr($output, 0, $key_length);
    else
      return bin2hex(substr($output, 0, $key_length));
  }

}
