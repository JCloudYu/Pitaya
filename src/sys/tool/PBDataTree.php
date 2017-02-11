<?php
	class PBDataTree implements ArrayAccess {
	
		private $_storage		 = NULL;
		private $_forceOverwrite = TRUE;
		public function __construct($default = array(), $forceOverwrite = TRUE)
		{
			$this->_storage = (is_array($default)) ? $default : array();
			$this->_forceOverwrite = empty($forceOverwrite) ? FALSE : TRUE;
		}



		public function &get($offset = NULL)
		{
			if (empty($offset))
				return $this->_storage;

			$offsets = explode('.', $offset);
			return $this->_travel($offsets);
		}
		public function set($offset, $value)
		{
			if (is_a($value, 'PBDataTree'))
				$value = $value->_storage;

			if ($value === NULL)
			{
				$this->clear($offset);
				return;
			}



			if (empty($offset))
			{
				if (is_array($value))
					$this->_storage = $value;

				return;
			}

			$offsets	 = explode('.', $offset);
			$finalOffset = array_pop($offsets);

			$target = &$this->_travel($offsets);

			if ($target === NULL)
				return;
			else
				$target[$finalOffset] = $value;
		}
		public function clear($offset)
		{
			if (empty($offset))
			{
				$this->_storage = array();
				return TRUE;
			}

			$offsets	 = explode('.', $offset);
			$finalOffset = array_pop($offsets);

			$target = &$this->_storage;
			foreach ($offsets as $offset)
			{
				if (!isset($target[$offset]) || !is_array($target[$offset]))
					return FALSE;

				$target = &$target[$offset];
			}

			unset($target[$finalOffset]);

			return TRUE;
		}
		public function valid($offset)
		{
			if (empty($offset)) return TRUE;

			$offsets	 = explode('.', $offset);

			$target = &$this->_storage;
			foreach ($offsets as $offset)
			{
				if (!isset($target[$offset]) || !is_array($target[$offset]))
					return FALSE;

				$target = &$target[$offset];
			}
			return TRUE;
		}
		public function merge($offset, $ref, $deepMerge = FALSE, $forceMerge = TRUE)
		{
			if (is_a($ref, 'PBDataTree'))
				$ref = $ref->_storage;


			if (empty($offset))
			{
				if (!is_array($ref))
					return FALSE;

				$result = self::_merge($this->_storage, $ref, $deepMerge, $forceMerge);
				if ($result !== FALSE)
					$this->_storage = $result;
				else
					return FALSE;
			}
			else
			{
				$offsets	 = explode('.', $offset);
				$finalOffset = array_pop($offsets);

				$target = &$this->_travel($offsets);
				if ($target === NULL) return FALSE;

				if (!is_array($target[$finalOffset]) || !is_array($ref))
				{
					if ($forceMerge)
						$target[$finalOffset] = $ref;
					else
						return FALSE;
				}


				$result = self::_merge($target[$finalOffset], $ref, $deepMerge, $forceMerge);

				if ($result !== FALSE)
					$target[$finalOffset] = $result;
				else
					return FALSE;
			}

			return TRUE;
		}
		
		private static function _merge($target, $ref, $deepMerge = FALSE, $forceMerge = TRUE)
		{
			$merged = $target;
			foreach ($ref as $idx => $value)
			{
				if (!isset($target[$idx]))
					$merged[$idx] = $value;
				else
				{
					if (is_array($target[$idx]) && is_array($value) && $deepMerge)
					{
						$result = self::_merge($target[$idx], $value, $deepMerge, $forceMerge);
						if ($result !== FALSE)
							$merged[$idx] = $result;
						else
							return FALSE;
					}
					else
					if ($forceMerge)
						$merged[$idx] = $value;
					else
						return FALSE;
				}
			}
			return $merged;
		}
		private function &_travel($offsets)
		{
			if (empty($offsets)) return $this->_storage;

			$finalOffset = array_pop($offsets);


			$target = &$this->_storage;
			foreach ($offsets as $offset)
			{
				if (!isset($target[$offset]))
					$target[$offset] = array();
				else
				if (!is_array($target[$offset]))
				{
					if ($this->_forceOverwrite)
						$target[$offset] = array();
					else
						return NULL;
				}

				$target = &$target[$offset];
			}

			if (!isset($target[$finalOffset]))
				$target[$finalOffset] = array();


			return $target[$finalOffset];
		}



		public function offsetGet($offset) { return $this->get($offset); }
		public function offsetSet($offset, $value) { $this->set($offset, $value); }
		public function offsetUnset($offset) { $this->clear($offset); }
		public function offsetExists($offset) { return $this->valid($offset); }
	}
