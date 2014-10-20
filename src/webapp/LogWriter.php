<?php

namespace tdt4237\webapp;

class LogWriter
{
	private $out;

    function __construct()
    {
		$this->out = fopen('log/app.log', 'a');
    }

	public function write($message)
	{
		fwrite($this->out, date('[Y-m-d G:i:s] '));
		fwrite($this->out, $message);
		fwrite($this->out, "\n");
	}

}

