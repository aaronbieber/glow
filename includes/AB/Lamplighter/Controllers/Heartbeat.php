<?php
namespace AB\Lamplighter\Controllers;

class Heartbeat {
  public function get() {
    $who = $this->get_name_by_ua();

    if (!empty($who)) {
      echo $this->get_heartbeat_by_name($who);
    }
  }

  public function set() {
    $success = false;
    $who = $this->get_name_by_ua();

    if (!empty($who)) {
      $ts = time();
      $exists = !empty($this->get_heartbeat_by_name($who));

      if ($exists) {
        $success = $this->update_heartbeat($who, $ts);
      } else {
        $success = $this->create_heartbeat($who, $ts);
      }
    }

    if ($success) {
      echo "$who = $ts. OK.\n";
    } else {
      echo "UH OH.\n";
    }
  }

  private function create_heartbeat($who, $ts) {
    $dbh = new \PDO('sqlite:data/heartbeat.db');
    $sth = $dbh->prepare('INSERT INTO heartbeats VALUES (:who, :ts)');
    return $sth->execute([ ':who' => $who, ':ts' => $ts ]);
  }

  private function update_heartbeat($who, $ts) {
    $dbh = new \PDO('sqlite:data/heartbeat.db');
    $sth = $dbh->prepare('UPDATE heartbeats SET ts = :ts WHERE who = :who');
    return $sth->execute([ ':who' => $who, ':ts' => $ts ]);
  }

  private function get_heartbeat_by_name($who) {
    $dbh = new \PDO('sqlite:data/heartbeat.db');
    $sth = $dbh->prepare('SELECT ts FROM heartbeats WHERE who = :who');
    $sth->execute([ ':who' => $who ]);
    $row = $sth->fetch(\PDO::FETCH_ASSOC);

    if (!empty($row['ts'])) {
      return $row['ts'];
    }

    return [];
  }

  private function get_name_by_ua() {
    $ua = $_SERVER['HTTP_USER_AGENT'];

    if (stristr($ua, 'Nexus')) {
      $who = 'aaron';
    } elseif (stristr($ua, 'Galaxy')) {
      $who = 'veronica';
    } else {
      $who = 'unknown';
    }

    return $who;
  }
}