<?php

/**
 * 404 Handler
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class webpage_notfound extends webpage {

  public function buildHTML() {
    http_response_code(404);
    $this->setTitle("Resource not found");
    $res = $_SERVER['REQUEST_URI'];
    $srv = $_SERVER['SERVER_SOFTWARE'];
    $name = $_SERVER["SERVER_NAME"];
    $port = $_SERVER["SERVER_PORT"];
    return <<< EOT
    <div class="row">
		  <div class="col-md-12 column">
			  <h3>404 - Resource not found</h3>
        <p>The requested resource $res was not found on this server.</p>
        <p><em>$srv Server at $name:$port.</em></p>
      </div>
    </div>
EOT;
  }

}
