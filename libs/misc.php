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

function login_blurb() {
  global $context;
  if ($context['logged']) {
    echo '<li><a href="./?p=control">Control Room</a></li>
            <li><a>Welcome ' . $context['name'] . ' <img src="http://spiderwebforums.ipbhost.com/uploads/profile/photo-' . $context['sw_id'] . '.png" alt="" style="max-height:22px;"/></a></li>';
  } else {
    echo '<li><a href="./?p=auth">Log in</a></li>';
  }
}