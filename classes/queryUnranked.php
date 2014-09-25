<?php

/**
 * Show unranked scenarios (<5 reviews)
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class queryUnranked extends queryCSR {

  public function _query($args) {
    $query = 'SELECT *, (B+G+A+S+P) as sum FROM composite where (B+G+A+S+P)<5 order by (B+G+A+S+P) desc;';
    $res = & $this->db->query($query);
    $this->res = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
  }

}
