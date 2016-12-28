<?php
namespace sometools {
	
	class logger {
		
		public $debug = false;
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
		function &debug($string) {
			if ($this->debug) {
				return $this->_pushlog($string, 'debug');
			} else {
				return new entry();
			}
		}
		
		/**
		 * stores a warning
		 * 
		 * this a kind of an error wich does not abort the process, and should be displayed to the USER
		 * 
		 * @param <string> $string
		 */
		function warning($string) {
			return  $this->_pushlog($string, 'warning');
		}
		
		/**
		 * store generic log information
		 * 
		 *
		 * @param <string> $string
		 * @param <string> $type (default|warning|danger|success|...)
		 */
		function &log($string, $type = 'info') {
			return $this->_pushlog($string, $type);
		}
		
		private function &_pushlog($string, $type) {
			if (gettype($string) !== "string") {
				$string = '<pre>' . print_r($string, 1) . '</pre>';
			}
		
			$item = new entry();
			$item->text = $string;
			$item->type = $type;
			
			if ($this->debug or $item->logTimestamps) {
				$item->timestamp = $this->_timestamp();
			}
			if ($this->debug) {
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
				$this->debug = $debug;
			}
		}
		
		function dump() {
			return implode("\n", $this->log);
		}
		
		function getWarnings() {
			return array_filter($this->log, function($a) {
				return (in_array($a->type, array('warning', 'error', 'danger')));
			});
		}
		
		function dumpWarnings() {
			foreach ($this->getWarnings() as $warning) {
				$warning->dump();
			}
		}
		
		function dumpLog() {
			foreach ($this->log as $entry) {
				$entry->dump();
			}
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
		
		public function dump() {
			$t = ($this->timestamp) ? "<span class='timestamp'>" . str_pad($this->timestamp, 18, '0', STR_PAD_RIGHT) . "</span>" : '';
			echo "<div class='alert alert-{$this->type}'>$t{$this->text}</div>";
			if ($this->debuginfo) {
				echo "<pre>{$this->debuginfo}</pre>";
			}
		}
	}
}