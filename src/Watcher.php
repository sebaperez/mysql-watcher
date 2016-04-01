<?php

class Watcher {

	public function set($user, $pass, $db, $server = "localhost") {
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		$this->server = $server;

		$this->result = "";
	}
	public function watch($query) {
		$this->query = $query;
		if ($conex = $this->getConex()) {
			$this->_watch();
		}
	}
	private function error($e) {
		$this->out("Error: " . $e);
		return false;
	}
	private function out($s) {
		echo("\n" . $s);
	}
	private function getConex() {
		if (isset($this->conex)) {
			return $this->conex;
		} else {
			$conex = new mysqli($this->server, $this->user, $this->pass, $this->db);
			if ($conex->connect_error) {
				return $this->error("Conection failed: " . $conex->connect_error);
			} else {
				$this->conex = $conex;
				return $conex;
			}
		}
	}
	private function _query($query) {
		$st = $this->conex->query($query);
		$st->data_seek(0);
		return $st;
	}
	private function getTables($query) {
		$st = $this->_query("EXPLAIN " . $query);
		$tables = [];
		while ($row = $st->fetch_assoc()) {
			array_push($tables, $row["table"]);
		}
		return $tables;
	}
	private function outdated() {
		$st = $this->_query($this->query);
		$s = "";
		while ($row = $st->fetch_assoc()) {
			$s .= implode("", $row);
		}
		$s = md5($s);
		if ($this->result != $s) {
			$this->result = $s;
			return true;
		} else {
			return false;
		}
	}
	private function update() {
		passthru('clear');
		$this->out("Watching: " . $this->query . "\n\n");
		passthru("mysql -e '" . $this->query . "' " . $this->db);
	}
	private function _watch() {
		while(1) {
			if ($this->outdated()) {
				$this->update();
			}
			sleep(1);
		}
	}
}

?>
