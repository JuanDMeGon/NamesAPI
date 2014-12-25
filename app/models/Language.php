<?php
	class Language extends Moloquent
	{
		protected $fillable = array(
										'name',
										'code',
										'nativename'
									);
		protected $guarded = array('_id');

		public function setAttributes($name, $code, $nativename)
		{
			$this->_id = sha1($name.$code);
			$this->name = $name;
			$this->code = $code;
			$this->nativename = $nativename;
		}
	}