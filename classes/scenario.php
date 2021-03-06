<?php

/**
 * Handles all scenario data
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class scenario extends CSR {
  public $exists=true;
  public $tid;
  public $title;
  public $tags;
  public $bgasp;
  public $author = array();
  public $dlWin;
  public $dlMac;

  function __construct($tid) {
    parent::__construct();
    $this->tid = $tid;
    $this->populateVars();
  }

  function populateVars() {
    // Check database
    $query = 'SELECT * FROM composite WHERE tid='
      . $this->db->quote($this->tid, 'integer') . ';';
    $res = & $this->db->query($query);

    if ($res->numRows() > 0) {
      $resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
      $bgasp = array(
        B => (int) $resu['b'],
        G => (int) $resu['g'],
        A => (int) $resu['a'],
        S => (int) $resu['s'],
        P => (int) $resu['p'],
      );

      // build tags
      $query = 'SELECT * FROM tags WHERE tid='
        . $this->db->quote($this->tid, 'integer') . ';';
      $tag = & $this->db->queryCol($query, null, 'tag');
      $this->title = $resu['title'];
      $this->tags = $tag;
      $this->bgasp = $bgasp;
      
      // build authorship
      $query = 'SELECT * FROM topic_author join author on topic_author.author = author.aid WHERE topic='
        . $this->db->quote($this->tid, 'integer') . ';';
      $res = & $this->db->query($query);
      while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
        $this->author[$resu['aid']] = $resu['name'];
      }
      
      // build downloads
      $query = 'SELECT dlWin as win, dlMac as mac FROM topic WHERE tid='
        . $this->db->quote($this->tid, 'integer') . ';';
      $res = & $this->db->query($query);
      while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
        $this->dlWin = $resu['win'];
        $this->dlMac = $resu['mac'];
      }
    } else {
      $this->exists = false;
    }
  }

  function ConstToStr($c) {
    foreach ($this->map as $k => $v) {
      if ($v == $c) {
        return $k;
      }
    }
  }

  function bgaspBB() {
    $val = 0;
    $pct = array();
    $tot = array_sum($this->bgasp);

    $pts = array(
      B => 5,
      G => 4,
      A => 3,
      S => 2,
      P => 1,
    );
    foreach ($this->bgasp as $cat => $num) {
      $pct[$cat] = $num / $tot;
      $val += $num * $pts[$cat];
    }
    $csr = $val / $tot;
    $ret = "Composite Score: [b]" . number_format($csr, 1) . "[/b]/5.0" . PHP_EOL . PHP_EOL;
    foreach ($this->bgasp as $cat => $num) {
      $ret .= $this->ConstToStr($cat) . ": [b]" . number_format($pct[$cat] * 100, 2) . "%[/b] [i](" . $num . "/" . $tot . ")[/i]" . PHP_EOL;
    }
    $ret .= '[encouragenecro]'.PHP_EOL;
    return $ret;
  }

  /**
   * Generate a [composite] BBcode tag ready for injections into the post
   * 
   * @return string Payload ready for insertion
   */
  function getPostPayload() {
    $data = array(
      'title' => $this->title,
      'tid' => $this->tid,
      'tags' => $this->tags,
      'bgasp' => $this->bgasp,
    );
    $payload = base64_encode(json_encode($data));
    return '[composite=' . $payload . ']' . PHP_EOL . $this->bgaspBB() . PHP_EOL . '[/composite]';
  }

  function updateOP() {
    foreach (htmlqp(file_get_contents($this->config['ipbURL'].'/topic/' . $this->tid . '-/'), '#replyNumContainer') as $item) {
      if ($item->attr("data-reply-num") == 1) {
        $pid = $item->attr("data-pid");
        $fid = $item->attr("data-fid");
      }
    }

    $edit = new IPB($this->tid, $fid, $pid);
    $edit->csrThread($this->getPostPayload());
  }

}
