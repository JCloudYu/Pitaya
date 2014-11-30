<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cloud
 * Date: 13/2/4
 * Time: PM11:56
 * To change this template use File | Settings | File Templates.
 */

class PBProcess extends PBObject
{
	private $_processId = NULL;
	private $_mainModuleId = NULL;
	private $_attachedModules = array();
	private $_system = NULL;

	private $_processState = 'waiting';

	private $_bootSequence = NULL;

	/**
	 * Get the process with specified process id
	 *
	 * @param string|null $id the specified process id
	 *
	 * @return PBProcess | null the specified PBProcess object
	 */
	public static function Process($id = NULL) { return SYS::Process($id); }

	public static function Execute($module, $request = NULL, $reusable = FALSE, $pId = NULL) {

		if (!is_a($module, "PBModule")) $module = SYS::Process($pId)->getModule("{$module}", $reusable);

		$module->prepare($request);
		return $module->exec(NULL);
	}

	public static function Render($module, $request = NULL, $reusable = FALSE, $pId = NULL) {

		if (!is_a($module, "PBModule")) $module = SYS::Process($pId)->getModule("{$module}", $reusable);

		$module->prepare($request);
		$result = $module->exec(NULL);
		
		echo "<div class='module {$module->class_lower}'>{$result}</div>";
	}

	public function __construct() {

		$this->_bootSequence = PBLList::GENERATE();
	}

	public function __destruct() {


	}

//SEC: Process API//////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __get_id() {

		return $this->_processId;
	}

	public function getModule($moduleName, $reusable = TRUE) {

		return $this->_acquireModule($moduleName, $reusable);
	}

	public function getNextModule()
	{
		if (!PBLinkedList::NEXT($this->_bootSequence)) return NULL;
		$moduleId = $this->_bootSequence->data['data'];
		PBLinkedList::PREV($this->_bootSequence);

		return $this->_attachedModules[$moduleId];
	}

	public function transferRequest($moduleRequest)
	{
		PBLinkedList::NEXT($this->_bootSequence);
		$this->_bootSequence->data['request'] = $moduleRequest;
		PBLinkedList::PREV($this->_bootSequence);
	}

	public function assignNextModule($moduleHandle, $moduleRequest = NULL)
	{
		if (is_a($moduleHandle, 'PBModule')) $moduleHandle = $moduleHandle->id;
		if (!array_key_exists($moduleHandle, $this->_attachedModules)) $moduleHandle = $this->_acquireModule($moduleHandle, TRUE)->id;


		$status = TRUE;
		$doPrepare = ($this->_processState == 'running') ? TRUE : FALSE;
		$status = $status && PBLinkedList::AFTER($this->_bootSequence, array('prepared' => $doPrepare, 'data' => $moduleHandle, 'request' => $moduleRequest), $moduleHandle);



		if (!$doPrepare) return $status;



		$status = $status && PBLList::NEXT($this->_bootSequence);

		if ($status)
		{
			$module = $this->_attachedModules[$moduleHandle];

			switch (SERVICE_EXEC_MODE)
			{
				case 'EVENT':
					$module->prepareEvent($moduleRequest);
					break;

				case 'NORMAL':
				default:
					$module->prepare($moduleRequest);
					break;
			}

			$status = $status && PBLList::PREV($this->_bootSequence);
		}

		return $status;
	}

	public function assignNextModules($moduleAry)
	{
		if (!is_array($moduleAry))
			throw(new Exception("Input parameter must be an array!"));


		$moduleAry = array_reverse($moduleAry);

		foreach ($moduleAry as $requestPair)
			$this->assignNextModule(@$requestPair['module'], @$requestPair['request']);
	}

	public function cancelNextModule() {

		$status = PBLList::NEXT($this->_bootSequence);
		if(!$status) return $status;

		$status = $status && PBLList::REMOVE($this->_bootSequence);
		return $status;
	}

	public function cancelFollowingModules() {
		while (PBLList::NEXT($this->_bootSequence))
			PBLList::REMOVE($this->_bootSequence);
	}

	public function replaceNextModule($moduleHandle, $moduleRequest = NULL)
	{
		if (is_a($moduleHandle, 'PBModule')) $moduleHandle = $moduleHandle->id;
		if(!array_key_exists($moduleHandle, $this->_attachedModules)) $moduleHandle = $this->_acquireModule($moduleHandle, TRUE)->id;

		$doPrepare = ($this->_processState == 'running') ? TRUE : FALSE;

		$status = TRUE;
		$status = $status && PBLList::NEXT($this->_bootSequence);

		if ($status)
		{
			$status = $status && PBLList::SET($this->_bootSequence, array('prepared' => $doPrepare, 'data' => $moduleHandle), $moduleHandle);

			if ($status && $doPrepare)
			{
				$module = $this->_attachedModules[$moduleHandle];
				switch (SERVICE_EXEC_MODE)
				{
					case 'EVENT':
						$module->prepareEvent($moduleRequest);
						break;

					case 'NORMAL':
					default:
						$module->prepare($moduleRequest);
						break;
				}
			}

			$status = $status && PBLList::PREV($this->_bootSequence);
		}

		return $status;
	}

	public function pushModule($moduleHandle, $moduleRequest = NULL)
	{
		if (is_a($moduleHandle, 'PBModule')) $moduleHandle = $moduleHandle->id;
		if(!array_key_exists($moduleHandle, $this->_attachedModules)) $moduleHandle = $this->_acquireModule($moduleHandle, TRUE)->id;


		$doPrepare = ($this->_processState == 'running') ? TRUE : FALSE;

		$status = PBLList::PUSH($this->_bootSequence, array('prepared' => $doPrepare, 'data' => $moduleHandle), $moduleHandle);



		if (!$doPrepare) return $status;



		if ($status)
		{
			$module = $this->_attachedModules[$moduleHandle];

			switch (SERVICE_EXEC_MODE)
			{
				case 'EVENT':
					$module->prepareEvent($moduleRequest);
					break;

				case 'NORMAL':
				default:
					$module->prepare($moduleRequest);
					break;
			}
		}

		return $status;
	}
//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// MARK: Friend(SYS)
	public function run() {

		if(!$this->friend('SYS'))
			throw(new Exception("Calling an inaccessible method PBProcess::run()."));

		if($this->_processId === NULL)
			throw(new Exception("The process has no module to execute!."));

		$this->_processState = 'running';

		switch (SERVICE_EXEC_MODE)
		{
			case 'EVENT':
				$dataInput = array('propagation' => TRUE);

				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data['data'];
					$dataInput = $this->_attachedModules[$moduleHandle]->event($dataInput);
					if ( !is_array($dataInput) )
						$dataInput = array('propagation' => TRUE, 'data' => $dataInput);
					$dataInput['propagation'] = !empty($dataInput['propagation']);

					// INFO: Stop propagation
					if ( empty($dataInput['propagation']) ) break;
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;

			case 'NORMAL':
			default:
				$dataInput = NULL;

				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data['data'];
					$dataInput = $this->_attachedModules[$moduleHandle]->exec($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
		}

		$this->_processState = 'waiting';

		return 'terminated';
	}

	// MARK: Friend(SYS)
	public function attachMainService($moduleName, $moduleRequest) {

		if(!$this->friend('SYS')) throw(new Exception("Calling an inaccessible function PBProcess::attachMainModule()."));

		if($this->_mainModuleId != NULL) throw(new Exception("Reattachment of main module is not allowed"));

		// INFO: Reference the definition file comes along with the service
		if(available("service.env")) using("service.env");

		// INFO: System will first look for [ main ] module in the service folder
		// INFO: If the main module doesn't exist, look for module with the service name instead
		$module = $this->_system->acquireModule($moduleName, $moduleName, TRUE);

		$module->__processInst = $this;

		// INFO: Preparing the module will force the module to it's corresponding bootstrap
		$moduleId = $module->id;
		$this->_mainModuleId = $moduleId;

		$this->_attachedModules[$moduleName] = $module;
		$this->_attachedModules[$moduleId] = $module;

		PBLList::PUSH($this->_bootSequence, array('prepared' => TRUE, 'data' => $moduleId), $moduleId);

		switch (SERVICE_EXEC_MODE)
		{
			case 'EVENT':
				$module->prepareEvent($moduleRequest);
				break;

			case 'NORMAL':
			default:
				$module->prepare($moduleRequest);
				break;
		}

		// INFO: Get default boot sequence from boot module
		$this->__bootSequence = $module->__bootSequence;
	}

	// MARK: Friend(SYS)
	public function __set___processId($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __processId."));

		$this->_processId = $value;
	}

	// MARK: Friend(SYS)
	public function __set___sysAPI($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __sysAPI."));

		$this->_system = $value;
	}

	// INFO: Parse and prepare bootSequence
	protected function __set___bootSequence($value) {

		if(is_null($value) || !is_array($value)) return;

		foreach($value as $illustrator)
		{
			if(!is_array($illustrator))
				throw(new Exception("Error bootSequence structure definition"));

			if(!array_key_exists('module', $illustrator))
				throw(new Exception("Error bootSequence structure definition"));



			$moduleHandle = $illustrator['module'];

			if (is_a($moduleHandle, 'PBModule') && array_key_exists($moduleHandle->id, $this->_attachedModules))
				$moduleId = $moduleHandle->id;
			else
			{
				$reuse = TRUE;
				if(array_key_exists('reuse', $illustrator))
				{
					if(!is_bool($illustrator['reuse']))
						throw(new Exception("Error bootSequence structure definition"));

					$reuse = $reuse && $illustrator['reuse'];
				}

				$moduleId = $this->_acquireModule($moduleHandle, $reuse)->id;
			}

			$request = (array_key_exists('request', $illustrator)) ? $illustrator['request'] : NULL;

			PBLList::PUSH($this->_bootSequence,  array('prepared' => FALSE, 'data' => $moduleId, 'request' => $request), $moduleId);
		}

		PBLinkedList::HEAD($this->_bootSequence);

		while (PBLinkedList::NEXT($this->_bootSequence))
		{
			$data	 = $this->_bootSequence->data;

			if (empty($data['prepared']))
			{
				$handle = $data['data'];
				$request = $data['request'];

				switch (SERVICE_EXEC_MODE)
				{
					case 'EVENT':
						$this->_attachedModules[$handle]->prepareEvent($request);
						break;

					case 'NORMAL':
					default:
						$this->_attachedModules[$handle]->prepare($request);
						break;
				}
			}
		}

		PBLinkedList::HEAD($this->_bootSequence);
	}

	private function _acquireModule($moduleName, $reusable = TRUE)
	{
		$reqModuleNames = explode('.', $moduleName);

		if (count($reqModuleNames) <= 1)
			$reqModuleNames = array($moduleName, $moduleName);
		else
			$reqModuleNames = array(array_shift($reqModuleNames), ($moduleName = implode('', $reqModuleNames)));

		if(array_key_exists($moduleName, $this->_attachedModules) && $reusable)
			return $this->_attachedModules[$moduleName];

		$module = $this->_system->acquireModule($reqModuleNames[0], $reqModuleNames[1]);
		$module->__processInst = $this;
		$moduleId = $module->id;

		$this->_attachedModules[$moduleId] = $module;

		if($reusable)
			$this->_attachedModules[$moduleName] = $module;

		return $module;
	}
}
