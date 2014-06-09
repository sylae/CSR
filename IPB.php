<?php

/**
 * Class for editing SW posts
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class IPB extends CSR {

  protected $tid;
  protected $fid;
  protected $pid;
  private $cookies;
  private $form;

  function __construct($tid, $fid, $pid) {
    parent::__construct();
    $this->tid = $tid;
    $this->pid = $pid;
    $this->fid = $fid;

    $this->_login();
    $this->_editForm();
  }

  function _login() {
    $url = "http://spiderwebforums.ipbhost.com/index.php?app=core&module=global&section=login";
    $html = file_get_contents($url);

    $auth = htmlqp($html, 'input[name=\'auth_key\']')->attr("value");

    $request = new HTTP_Request2('http://spiderwebforums.ipbhost.com/index.php?app=core&module=global&section=login&do=process');
    $request->setMethod(HTTP_Request2::METHOD_POST)
      ->setConfig('follow_redirects', true)
      ->setCookieJar()
      ->addPostParameter('auth_key', $auth)
      ->addPostParameter('ips_username', $this->config['ipbUser'])
      ->addPostParameter('ips_password', $this->config['ipbPass']);

    $response = $request->send();

    // TODO: Error checking
    // assume it works
    $this->cookies = $response->getCookies();
    return true;
  }

  function _editForm() {
    $edit = new HTTP_Request2(
      sprintf('http://spiderwebforums.ipbhost.com/index.php?app=forums&module=post&section=post&do=edit_post&f=%s&t=%s&p=%s&st=', $this->fid, $this->tid, $this->pid));
    $edit->setMethod(HTTP_Request2::METHOD_GET)
      ->setConfig('follow_redirects', true);

    foreach ($this->cookies as $arCookie) {
      $edit->addCookie($arCookie['name'], $arCookie['value']);
    }

    $response = $edit->send();
    $this->form = htmlqp($response->getBody());
  }
  
  function csrThread($p) {
    $this->body = $this->_injectPayload(htmlqp($this->form, 'textarea[name=\'Post\']')->text(), $p);
    preg_match('/"existingTags":\\[(.*?)\\]/', $this->form->html(), $matches);
    $this->tags = str_replace('"', '', $matches[1]);
    $this->edit = $this->_edit("IPB::csrThread");
    $this->_submitEdit();
  }
  
  function csrAll($p) {
    $this->body = $p;
    preg_match('/"existingTags":\\[(.*?)\\]/', $this->form->html(), $matches);
    $this->tags = str_replace('"', '', $matches[1]);
    $this->edit = $this->_edit("IPB::csrAll");
    $this->_submitEdit();
  }
  
  function _edit($w) {
    $str = "Automated Sybot edit %s; worker %s";
    return sprintf($str, date('c'), $w.'/'.php_uname('n'));
  }

  function _injectPayload($source, $payload) {
    $test = array();
    if (preg_match('/(\\[composite=.*?\\].*?\\[\\/composite\\])/s', $source, $test)) {
      $ret = str_replace($test[1], $payload, $source);
    } elseif (preg_match('/(Composite Score.*)Keywords/s', $source, $test) || preg_match('/(Composite Score.*)/s', $source, $test)) {
      $ret = str_replace($test[1], $payload.PHP_EOL.PHP_EOL, $source);
    }
    return $ret;
  }

  function _submitEdit() {
    $send = new HTTP_Request2('http://spiderwebforums.ipbhost.com/index.php?');
    $send->setMethod(HTTP_Request2::METHOD_POST)
      ->setConfig('follow_redirects', true)
      ->addPostParameter('add_edit', 1)
      ->addPostParameter('post_edit_reason', $this->edit)
      ->addPostParameter('TopicTitle', htmlqp($this->form, '#topic_title')->attr('value'))
      ->addPostParameter('ipsTags', $this->tags)
      ->addPostParameter('Post', $this->body)
      ->addPostParameter('app', 'forums')
      ->addPostParameter('module', 'post')
      ->addPostParameter('section', 'post')
      ->addPostParameter('do', 'edit_post_do')
      ->addPostParameter('s', htmlqp($this->form, 'input[name=\'s\']')->attr('value'))
      ->addPostParameter('p', htmlqp($this->form, 'input[name=\'p\']')->attr('value'))
      ->addPostParameter('t', htmlqp($this->form, 'input[name=\'t\']')->attr('value'))
      ->addPostParameter('f', htmlqp($this->form, 'input[name=\'f\']')->attr('value'))
      ->addPostParameter('parent_id', htmlqp($this->form, 'input[name=\'parent_id\']')->attr('value'))
      ->addPostParameter('attach_post_key', htmlqp($this->form, 'input[name=\'attach_post_key\']')->attr('value'))
      ->addPostParameter('auth_key', htmlqp($this->form, 'input[name=\'auth_key\']')->attr('value'))
      ->addPostParameter('st', '0')
      ->addPostParameter('dosubmit', 'Submit Modified Post');
    foreach ($this->cookies as $arCookie) {
      $send->addCookie($arCookie['name'], $arCookie['value']);
    }
    $send->send();
  }

}
