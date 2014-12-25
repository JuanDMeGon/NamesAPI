<?php
	class Country extends Moloquent
	{
		protected $fillable = array(
										'name',
										'iso2',
										'iso3',
										'code',
										'region'
									);
		protected $guarded = array('_id');

		public function setAttributes($name, $iso2, $iso3, $code, $region)
		{
			$this->_id = sha1($name.$iso2);
			$this->name = $name;
			$this->iso2 = $iso2;
			$this->iso3 = $iso3;
			$this->code = $code;
			$this->region = $region;
		}
	}