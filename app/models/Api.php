<?php
	class Api
	{
		public static function microtime_float()
	    {
	        list($useg, $seg) = explode(" ", microtime());
	        return ((float)$useg + (float)$seg);
	    }
	}