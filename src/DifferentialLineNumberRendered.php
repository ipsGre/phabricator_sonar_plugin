<?php

final class DifferentialLineNumberRendered {

  private $issues;
  private $total;
  private $critical;
  private $major;
  private $minor;
  private $info;
  private $blocker;
  private $lines;

  const SONAR_URL = 'http://yolosonar.com';
  const PROJECTS_ROOT = 'com.yoloprojects:';
  const SEARCH_REQUEST = '/api/issues/search?components=';

  public function __construct($path, $range_start, $range_len) {
    $this->critical = 0;
    $this->major = 0;
    $this->minor = 0;
    $this->info = 0;
    $this->blocker = 0;
    $this->total = 0;
      
    $src = str_replace('/trunk/', ':', $path);

    $request = array();
    $request[] = self::SONAR_URL;
    $request[] = self::SEARCH_REQUEST;
    $request[] = self::PROJECTS_ROOT;
    $request[] = $src;

    $url = implode('', $request);
    $content = file_get_contents($url);
    $json = json_decode($content);
    $this->issues = $json->issues;
    
    $this->lines = array();
    for ($ii = $range_start; $ii < $range_start + $range_len; $ii++) {
      $this->lines[$ii] = $ii;
    }
    if ($this->issues) {
      foreach ($this->issues as $k => $v) {
        if (isset($v->line) && $v->status == 'OPEN') {
           $num = $v->line;
           $type = $v->severity;
           $msg = $v->message;
           $this->lines[$num] = $this->iconRendered($type, $num, $msg, 1);
        } 
      }
    }
  }

  public function getInfoTag() {
    $res = array();
    if ($this->critical) {
      $res[] = $this->iconRendered('CRITICAL', $this->critical, 'Criticals', 0);
    }
    if ($this->major) {
      $res[] = $this->iconRendered('MAJOR', $this->major, 'Majors', 0);
    }
    if ($this->minor) {
      $res[] = $this->iconRendered('MINOR', $this->minor, 'Minors', 0);
    }
    if ($this->info) {
      $res[] = $this->iconRendered('INFO', $this->info, 'Infos', 0);
    }
    if ($this->blocker) {
      $res[]  = $this->iconRendered('BLOCKER', $this->blocker, 'Bloquers', 0); 
    }
    if ($this->total) {
      $res[] = pht(" (%d)", $this->total);
    }
    return phutil_tag('center', array(), $res);
  }

  public function iconRendered($type, $num, $msg, $plus) {
    $icon = null;
    $color = null;
    switch ($type) {
      case 'CRITICAL':
        $icon = 'fa-arrow-circle-up';
        $color = 'red';
        $this->critical+=$plus;
        $this->total+=$plus;
        break;
      case 'MAJOR':
        $icon = 'fa-chevron-circle-up';
        $color = 'red';
        $this->major+=$plus;
        $this->total+=$plus;
        break;
      case 'MINOR':
        $icon = 'fa-chevron-circle-down';
        $color = 'green';
        $this->minor+=$plus;
        $this->total+=$plus;
        break;
      case 'INFO':
        $icon = 'fa-arrow-circle-down';
        $color = 'green';
        $this->info+=$plus;
        $this->total+=$plus;
        break;
      case 'BLOCKER':
        $icon = 'fa-exclamation-circle';
        $color = 'red';
        $this->blocker+=$plus;
        $this->total+=$plus;
        break;
      
      default:
        return "error";
        break;
    }
    $icon = id(new PHUIIconView())
              ->setIconFont($icon, $color);
    if ($msg) {
      Javelin::initBehavior('phabricator-tooltips');
        $icon->addSigil('has-tooltip');
        $icon->setMetadata(
          array(
            'tip' => $msg,
            'size' => 8*strlen($msg),
            ));
    }
    return array($icon, ' ', $num);
  }

  public function getLineTag($num) {
    return id($this->lines[$num]);
  }
}