<?php
	class Name extends Moloquent
	{
		protected $fillable = array(
										'value',
										'popularity',
										'gender',
										'composition',
										'meaning',
										'procedence',
										'country',
										'language'
									);
		protected $guarded = array('_id');

		public function setAttributes($value, $popularity, $gender, $composition, $meaning, $procedence, $country, $language)
		{
			$this->_id = sha1($value.$gender.$country.$language);
			$this->value = $value;
			$this->popularity = $popularity;
			$this->gender = $gender;
			$this->composition = $composition;
			$this->meaning = $meaning;
			$this->procedence = $procedence;
			$this->country = $country;
			$this->language = $language;
		}

		public function getCountry()
		{
			return $this->country;
		}

		/*public function locations()
	    {
	        return $this->embedsMany('Location');
	    }

	    public function languages()
	    {
	        return $this->embedsMany('Language');
	    }*/
	}