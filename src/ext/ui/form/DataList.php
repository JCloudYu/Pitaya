<?php
/**
 * Pitaya - list.php
 * Created by JCloudYu on 2013/09/12 23:42
 */
	using('kernel.basis.PBObject');
	using('ext.base.misc');

	class DataList extends PBObject
	{
		const ALLOWED_COLUMN_TYPES = 'checkbox,radio';

		private $_columns = array();
		private $_data = array();
		private $_attr = array();

		private $_identifier = '';

		public function __construct($type = 'list')
		{
			static $instCounter = 0;
			$type = strtolower($type);
			$this->_identifier = substr(md5(uniqid() . ++$instCounter), 0, 16);
		}



		public function __set_identifier($value) { $this->_identifier = $value; }
		public function __get_identifier() { return $this->_identifier; }



		public function __set_header($value) { $this->_columns = $value; }
		public function __get_header() { return $this->_columns; }
		public function addColumn($column) { $this->_columns[] = $column; }



		public function __set_data($value) { $this->_data = $value; }
		public function __get_data() { return $this->_data; }
		public function addData($data) { $this->_data[] = $data; }



		public function __set_attr($value) { $this->_attr = $value; }
		public function __get_attr($value) { return $this->_attr; }
		public function addAttr($attrStr) { $this->_attr[] = $attrStr; }



		public function render()
		{
			$columns = array();

			$header = '';
			foreach ($this->_columns as $column)
			{
				$colProp = array();
				@$colProp['column-type'] = (in_array(strtolower(@$column['column-type']), explode(',', self::ALLOWED_COLUMN_TYPES))) ?
										  strtolower(@$column['column-type']) : '';

				@$colProp['data-type']	= (empty($column['data-type'])) ? 'raw' : $column['data-type'];
				@$colProp['width'] 		= (empty($column['width'])) ? '' : "width=\"{$column['width']}\"";
				@$colProp['align'] 		= (empty($column['align'])) ? '' : "style='text-align:{$column['align']}'";

				$columns[] = $colProp;


				@$header .= "<th {$colProp['width']} {$colProp['align']}>{$column['title']}</th>";
			}
			$header = empty($header) ? '' : "<tr>{$header}</tr>";


			$body = '';
			foreach ($this->_data as $rowData)
			{
				$rowHTML = '';
				foreach ($columns as $idx => $def)
				{
					$type = $def['data-type'];
					$width = $def['width'];
					$align = $def['align'];
					$checked = '';

					if (is_array(@$rowData[$idx]))
					{
						$value = (isset($rowData[$idx]['value'])) ? TO(@$rowData[$idx]['value'], $type) : '';
						$checked = (TO(@$rowData[$idx]['checked'], 'boolean')) ? 'checked' : '';
						$align = (isset($rowData[$idx]['align'])) ? $rowData[$idx]['align'] : $align;
					}
					else
						$value = TO(@$rowData[$idx], $type);

					switch ($def['column-type'])
					{
						case 'checkbox':
							$rowHTML .= "<td {$width} {$align}><input type='checkbox' value='{$value}' {$checked} rel='{$this->_identifier}' /></td>";
							break;
						case 'radio':
							$rowHTML .= "<td {$width} {$align}><input type='radio' value='{$value}' {$checked} rel='{$this->_identifier}' /></td>";
							break;
						default:
							$rowHTML .= "<td {$width} {$align}><span>{$value}</span></td>";
							break;
					}
				}
				$body .= "<tr>{$rowHTML}</tr>";
			}


			$attr = implode(' ', $this->_attr);

			return <<<HTML
				<table {$attr} rel='{$this->_identifier}'>
					<thead>{$header}</thead>
					<tbody>{$body}</tbody>
				</table>
HTML;
		}
	}