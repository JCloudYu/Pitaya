<?php
//	using('ext.base.misc');

	final class PBVarVault
	{
		const NORMAL_VAR = 'normal';
		const CONST_VAR  = 'const';

		private $_var_valt = NULL;
		public function __construct() { $this->_var_valt = array(); }

		// region [ Getters and Setters ]
		// INFO: Fast setter (without type casting)
		public function __set($name, $value) { $this->set($name, $value); }
		public function __get($name)
		{
			return array_key_exists($name, $this->_var_valt) ? $this->_var_valt[$name]['value'] : NULL;
		}
		public function __unset($name)
		{
			if (@$this->_var_valt[$name]['type'] !== self::CONST_VAR)
				unset($this->_var_valt[$name]);
		}


		// INFO: Normal getter and setters
		/**
		 * Get the stored variables and convert the value into specified type.
		 *
		 * @param string $name the variable name
		 * @param string $type the type the retrived value to be converted
		 * @param null $default the returned value once the target variable is not defined
		 *
		 * @return mixed|null the converted value of the secified variable
		 */
		public function get($name, $type = 'raw', $default = NULL)
		{
			return array_key_exists($name, $this->_var_valt) ? TO($this->_var_valt[$name]['value'], $type) : $default;
		}

		/**
		 * Set the variable name with the specified value
		 *
		 * @param string $name name of the specified variable
		 * @param mixed $value value to be stored in the variable
		 * @param bool $constant the flag indicates whether the variable is constant or not
		 *
		 * @throws Exception when the specified variable exists and is a constant
		 */
		public function set($name, $value, $constant = FALSE)
		{
			if (array_key_exists($name, $this->_var_valt))
			{
				if ($this->_var_valt[$name]['type'] == self::CONST_VAR)
					throw(new Exception("Writting value to a constant named '$name'!"));
			}
			else
				$this->_var_valt[$name] = array('type' => $constant ? self::CONST_VAR : self::NORMAL_VAR);

			$this->_var_valt[$name]['value'] = $value;
		}
		// endregion

		// INFO: Clear all the stored variables except the constants
		public function clear()
		{
			foreach ($this->_var_valt as $name => $content)
			{
				if ($this->_var_valt[$name]['type'] == self::NORMAL_VAR)
					unset($this->_var_valt[$name]);
			}
		}
	}