<?php

/**
 * Misc. functions to do...many...things. 
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

/**
 * Kill the process if we're over the web.
 */
function ensure_console() {
  if (PHP_SAPI != 'cli') {
    http_response_code(403);
    header("Content-Type: text/plain");
    die("This file cannot be called directly.");
  }
}