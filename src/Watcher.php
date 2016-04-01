<?php

class Watcher {

	public function set($user, $pass, $db, $server = "localhost") {
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		$this->server = $server;

		$this->result_md5 = "";
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
	private function getFormattedResponse($st) {
		$sf = "";
		$sizes = [];
		$row = $st->fetch_assoc();
		foreach ($row as $key => $value) {
			$sizes[$key] = strlen($key);
		}
		while ($row = $st->fetch_assoc()) {
			foreach ($row as $key => $value) {
				$length = strlen($value);
				if ($length > $sizes[$key]) {
					$sizes[$key] = $length;
				}
			}
		}
		$st->data_seek(0);

		foreach ($sizes as $length) {
			$sf .= "+" . str_pad("", $length + 2, "-");
		}
		$sf .= "+\n";
		
		$row = $st->fetch_assoc();
		foreach ($row as $key => $value) {
			$sf .= "| ";
			$sf .= str_pad($key, $sizes[$key] + 1);
		}
		$sf .= "|\n";

		foreach ($sizes as $length) {
			$sf .= "+" . str_pad("", $length + 2, "-");
		}
		$sf .= "+\n";

		do {
			foreach ($row as $key => $value) {
				$sf .= "| ";
				$sf .= str_pad($value, $sizes[$key] + 1);
			}
			$sf .= "|\n";
		} while ($row = $st->fetch_assoc());

		foreach ($sizes as $length) {
			$sf .= "+" . str_pad("", $length + 2, "-");
		}
		$sf .= "+\n";

		$st->data_seek(0);
		return $sf;
	}
	private function outdated() {
		$st = $this->_query($this->query);
		$s = "";
		$sf = $this->getFormattedResponse($st);
		while ($row = $st->fetch_assoc()) {
			$s .= implode("", $row);
		}
		$s = md5($s);
		if ($this->result_md5 != $s) {
			$this->result_md5 = $s;
			return $sf;
		} else {
			return false;
		}
	}
	private function update($sf) {
		passthru('clear');
		$this->out("Watching: " . $this->query . "\n\n");
		$this->out($sf);
	}
	private function _watch() {
		while(1) {
			if ($sf = $this->outdated()) {
				$this->update($sf);
			}
			sleep(1);
		}
	}
}

?>
