<?php

	/*
	Made by Patel (itsPATEL.com)
	Last Updated: 6/6/2013
	Github: https://github.com/nehalvpatel/cgminerPHP
	*/
	
	class cgminerPHP {
		var $_addr;
		var $_port;
		
		function __construct($addr, $port) {
			$this->_addr = $addr;
			$this->_port = $port;
			
			$this->testsock();
		}
		
		function testsock() {
			if (!@$this->request("version")) { die(); }
		}
		
		function getsock($addr, $port) {
			$socket = null;
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket === false || $socket === null) {
				$error = socket_strerror(socket_last_error());
				$msg   = "socket create(TCP) failed";
				echo "ERR: $msg '$error'\n";
				return null;
			}
			
			$res = socket_connect($socket, $this->_addr,$this->_port);
			if ($res === false) {
				$error = socket_strerror(socket_last_error());
				$msg   = "socket connect(" . $this->_addr . "," . $this->_port . ") failed";
				echo "ERR: $msg '$error'\n";
				socket_close($socket);
				return null;
			}
			return $socket;
		}
		
		function readsockline($socket) {
			$line = '';
			while (true) {
				$byte = socket_read($socket, 1);
				if ($byte === false || $byte === '')
					break;
				if ($byte === "\0")
					break;
				$line .= $byte;
			}
			return $line;
		}
		
		function request($cmd) {
			$socket = $this->getsock($this->_addr, $this->_port);
			if ($socket != null) {
				socket_write($socket, $cmd, strlen($cmd));
				$line = $this->readsockline($socket);
				socket_close($socket);
				
				if (strlen($line) == 0) {
					echo "WARN: '$cmd' returned nothing\n";
					return $line;
				}
				
				if (substr($line, 0, 1) == '{')
					return json_decode($line, true);
				
				$data = array();
				
				$objs = explode('|', $line);
				foreach ($objs as $obj) {
					if (strlen($obj) > 0) {
						$items = explode(',', $obj);
						$item  = $items[0];
						$id    = explode('=', $items[0], 2);
						if (count($id) == 1 or !ctype_digit($id[1]))
							$name = $id[0];
						else
							$name = $id[0] . $id[1];
						
						if (strlen($name) == 0)
							$name = 'null';
						
						if (isset($data[$name])) {
							$num = 1;
							while (isset($data[$name . $num]))
								$num++;
							$name .= $num;
						}
						
						$counter = 0;
						foreach ($items as $item) {
							$id = explode('=', $item, 2);
							if (count($id) == 2)
								$data[$name][$id[0]] = $id[1];
							else
								$data[$name][$counter] = $id[0];
							
							$counter++;
						}
					}
				}
				
				return $data;
			}
			
			return null;
		}
	}

?>