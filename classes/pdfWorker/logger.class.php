<?php
namespace sometools {
	
	class logger {
		
		public $debugmode = false;
		public $logTimestamps = true;
		
		public $log = [];
		
		private  $_lockstart;
		
		/**
		 * logs sensible information ONLY if debugmode is on
		 * 
		 * use this to log passwords, sql queries and stuff like this if debuggin'
		 * 
		 * @param <string> $string
		 */
		function debug($string) {
			if ($this->debugmode) {
				return $this->_pushlog($string, 'debug');
			} else {
				return new entry();
			}
		}
		
		/**
		 * stores a warning
		 * 
		 * this a kind of an error wich does not abort the process, and should be displayed to the user
		 * 
		 * @param <string> $string
		 */
		function warning($string) {
			return  $this->_pushlog($string, 'warning');
		}

		/**
		 * stores an error
		 *
		 * usually stops the process
		 * is shown to user
		 *
		 *
		 * @param <string> $string
		 */
		function error($string) {
			return  $this->_pushlog($string, 'danger');
		}
		
		/**
		 * store generic log information
		 * 
		 *
		 * @param <string> $string
		 * @param <string> $type (default|warning|danger|success|...)
		 */
		function log($string, $type = 'info') {
			return $this->_pushlog($string, $type);
		}
		
		private function _pushlog($string, $type) {
			if (gettype($string) !== "string") {
				$string = '<pre>' . print_r($string, 1) . '</pre>';
			}
		
			$item = new entry();
			$item->text = $string;
			$item->type = $type;
			
			if ($this->debugmode or $this->logTimestamps) {
				$item->timestamp = $this->_timestamp();
			}
			if ($this->debugmode) {
				$item->debuginfo = $this->_backtrace();
			}
			
			$this->log[] = $item;
		
			return $item;
		}
		
		
		private function _backtrace() {
			$bb = debug_backtrace();
			$re = [];
			foreach ($bb as $b) {
				@$re[] = "{$b["function"]} in {$b["file"]} line {$b["line"]}";
			}
			return implode("\n", $re);
		}
		
		
		private function _timestamp() {
			return (microtime(true) - $this->_lockstart);

		}
		
		function __construct($debug = null) {
			$this->_lockstart = microtime(true);
			if ($debug !== null) {
				$this->debugmode = $debug;
			}
		}
		
		function getWarnings() {
			return array_filter($this->log, function($a) {
				return (in_array($a->type, array('warning', 'error', 'danger')));
			});
		}
		
		function dumpWarnings($return = false, $text = false) {
			ob_start();
			foreach ($this->getWarnings() as $warning) {
				$warning->dump($text);
			}
			$dump = ob_get_clean();
			if (!$return) {
				echo $dump;
			}
			return $dump;
		}
		
		function dumpLog($return = false, $text = false) {
			ob_start();
			foreach ($this->log as $entry) {
				$entry->dump($text);
			}
			$dump = ob_get_clean();
			if (!$return) {
				echo $dump;
			}
			return $dump;
		}
		
	}
	
	class entry {
		public $type = 'info';
		public $text = '';
		public $timestamp;
		public $debuginfo = '';
	
		public function __invoke() {
			return $this->text;
		}
		
		public function dumpTEXT() {
			$t = ($this->timestamp) ? str_pad($this->timestamp, 18, '0', STR_PAD_RIGHT) : '';
			$a = str_pad($this->type, 12, ' ', STR_PAD_RIGHT);
			echo "\n$t $a {$this->text}";
			if ($this->debuginfo) {
				echo "\n{$this->debuginfo}";
			}
		}
		
		public function dump($text = false) {
			if ($text) {
				return $this->dumpTEXT();
			}
			
			$t = ($this->timestamp) ? "<span class='timestamp'>" . str_pad($this->timestamp, 18, '0', STR_PAD_RIGHT) . "</span>" : '';
			echo "<div class='alert alert-{$this->type}'>$t{$this->text}</div>";
			if ($this->debuginfo) {
				echo "<pre>{$this->debuginfo}</pre>";
			}
		}
	}
}