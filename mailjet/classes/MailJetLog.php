<?php

class MailJetLog
{
	static public $file;
	
	static public $handle;

	/**
	 * Init default value
	 * @static
	 */
	static public function init()
	{
		MailJetLog::$file = dirname(__FILE__).'/../logs/ajax.log';
	}

	static private function lockWrite($handle, $msg)
	{
		if ($handle && flock($handle, LOCK_EX))
		{
			fwrite($handle, $msg . "\r\n");
			flock($handle, LOCK_UN);
			return true;
		}
		return false;
	}

	static public function write($file, $message, $mode = 'a+', $close = false)
	{
		$date = date("d/m/Y G:i:s");
		$exist = false;
		if (file_exists($file))
			$exist = true;
		
		self::setHandle($file, $mode);
		
		if (!$exist)
			@chmod($file, 0666);

		if (self::$handle && self::lockWrite(self::$handle, '['.$date.'] '.$message))
		{
			if ($close)
				fclose(self::$handle);
			return true;
		}
		return false;
	}
	
	static private function setHandle($file, $mode)
	{
		if ($file == self::$file && !self::$handle)
			self::$handle = fopen($file, $mode);
		else if ($file != self::$file)
		{
			if (self::$handle)
				fclose($handle);
			self::$file = $file;
			self::$handle = fopen($file, $mode);
		}
	}
}