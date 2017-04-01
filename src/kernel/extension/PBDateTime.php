<?php
	class PBDateTime extends DateTime {
		public function __get( $prop ) {
			switch( strtolower($prop) ) {
				case "year":
					return $this->format( "Y" ) | 0;
					
				case "month":
					return $this->format( "m" ) | 0;
					
				case "day":
					return $this->format( "d" ) | 0;
				
				case "hour":
					return $this->format( "H" ) | 0;
				
				case "minute":
					return $this->format( "i" ) | 0;
				
				case "second":
					return $this->format( "s" ) | 0;
			}
			
			return $this->{$prop};
		}
		public function __set( $prop, $value ) {
			switch( strtolower($prop) ) {
				case "year":
					$this->setDate(intval($value), $this->month, $this->day);
					break;
					
				case "month":
					$this->setDate($this->year, intval($value), $this->day);
					break;
					
				case "day":
					$this->setDate($this->year, $this->month, intval($value));
					break;
				
				case "hour":
					$this->setTime(intval($value), $this->minute, $this->second);
					break;
				
				case "minute":
					$this->setTime($this->hour, intval($value), $this->second);
					break;
				
				case "second":
					$this->setTime($this->hour, $this->minute, intval($value));
					break;
			}
			
			return $this->{$prop} = $value;
		}
	}