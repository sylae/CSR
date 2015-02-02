<?php
/**
 * Class inclusions and things to do!
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require 'classes.php';

$page = preg_replace("/[^[:alnum:]]/ui", '', (array_key_exists('p', $_GET) ? $_GET['p'] : 'front'));
$c = 'webpage_'.$page;
$c = new $c;

?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $c->title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="libs/ext/jquery-release/jquery.min.js" type="text/javascript" charset="UTF-8"></script>
    <link href="libs/ext/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="libs/ext/bootstrap/dist/js/bootstrap.min.js"></script>
  </head>
  <body>

    <nav class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="./"><img src="img/boa.png" alt="" /></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="./?p=scenario">Scenarios</a></li>
            <li><a href="./?p=author">Authors</a></li>
            <li><a href="./?p=tags">Keywords</a></li>
            <li><a href="./?p=logs">Logs</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <?php echo login_blurb(); ?>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
      <?php echo $c->html; ?>
    </div>
  </body>
</html>
