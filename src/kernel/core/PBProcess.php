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

	private $_bootSequence = NULL;

	/**
	 * Get the process with specified process id
	 *
	 * @param string|null $id the specified process id
	 *
	 * @return PBProcess | null the specified PBProcess object
	 */
	public static function Process($id = NULL) { return SYS::Process($id); }

	public function __construct() {

		$this->_bootSequence = PBLList::GENERATE();
	}

	public function __destruct() {


	}

//SEC: Process API//////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __get_id() {

		return $this->_processId;
	}

	public function getModule($moduleName) {

		return $this->_acquireModule($moduleName, $reusable);
	}

	public function attachModule($moduleName, $moduleRequest = NULL, $reusable = TRUE) {

		$module = $this->_acquireModule($moduleName, $reusable);

		switch (SERVICE_EXEC_MODE)
		{
			case 'INSTALL':
				$module->prepareInstall($moduleRequest);
				break;
			case 'UPDATE':
				$module->prepareUpdate($moduleRequest);
				break;
			case 'PATCH':
				$module->preparePatch($moduleRequest);
				break;
			case 'UNINSTALL':
				$module->prepareUninstall($moduleRequest);
				break;
			case 'NORMAL':
			default:
				$module->prepare($moduleRequest);
				break;
		}

		return $module;
	}

	public function assignNextModule($moduleHandle)
	{
		if (is_a($moduleHandle, 'PBModule')) $moduleHandle = $moduleHandle->id;

		$handle = explode('.', $moduleHandle); array_shift($handle);
		$handle = (count($handle) >= 1) ? implode('', $handle) : $moduleHandle;

		if(!array_key_exists($handle, $this->_attachedModules)) return FALSE;

		PBLList::AFTER($this->_bootSequence, $handle, $handle);

		return TRUE;
	}

	public function cancelNextModule() {

		$status = PBLList::NEXT($this->_bootSequence);
		if(!$status) return $status;

		$status = $status && PBLList::REMOVE($this->_bootSequence);
		return $status;
	}

	public function replaceNextModule($moduleHandle)
	{
		$handle = explode('.', $moduleHandle); array_shift($handle);
		$handle = (count($handle) >= 1) ? implode('', $handle) : $moduleHandle;

		if(!array_key_exists($handle, $this->_attachedModules)) return FALSE;

		$status = PBLList::NEXT($this->_bootSequence);
		$status = $status && PBLList::SET($this->_bootSequence, $handle, $handle);
		$status = $status && PBLList::PREV($this->_bootSequence);

		return $status;
	}

	public function pushModule($moduleHandle)
	{
		$handle = explode('.', $moduleHandle); array_shift($handle);
		$handle = (count($handle) >= 1) ? implode('', $handle) : $moduleHandle;

		if(!array_key_exists($handle, $this->_attachedModules)) return FALSE;

		$status = PBLList::PUSH($this->_bootSequence, $handle, $handle);

		return $status;
	}
//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// MARK: Friend(SYS)
	public function run() {

		if(!$this->friend('SYS'))
			throw(new Exception("Calling an inaccessible method PBProcess::run()."));

		if($this->_processId === NULL)
			throw(new Exception("The process has no module to execute!."));

		$dataInput = NULL;

		switch (SERVICE_EXEC_MODE)
		{
			case 'INSTALL':
				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data;
					$dataInput = $this->_attachedModules[$moduleHandle]->install($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
			case 'UPDATE':
				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data;
					$dataInput = $this->_attachedModules[$moduleHandle]->update($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
			case 'PATCH':
				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data;
					$dataInput = $this->_attachedModules[$moduleHandle]->patch($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
			case 'UNINSTALL':
				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data;
					$dataInput = $this->_attachedModules[$moduleHandle]->uninstall($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
			case 'NORMAL':
			default:
				PBLList::HEAD($this->_bootSequence);
				do
				{
					$moduleHandle = $this->_bootSequence->data;
					$dataInput = $this->_attachedModules[$moduleHandle]->exec($dataInput);
				}
				while(PBLList::NEXT($this->_bootSequence));
				break;
		}

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

		PBLList::PUSH($this->_bootSequence, $moduleId, $moduleId);

		switch (SERVICE_EXEC_MODE)
		{
			case 'INSTALL':
				$module->prepareInstall($moduleRequest);
				break;
			case 'UPDATE':
				$module->prepareUpdate($moduleRequest);
				break;
			case 'PATCH':
				$module->preparePatch($moduleRequest);
				break;
			case 'UNINSTALL':
				$module->prepareUninstall($moduleRequest);
				break;
			case 'NORMAL':
			default:
				$module->prepare($moduleRequest);
				break;
		}

		// INFO: Get default boot sequence from boot module
		$this->__bootSequence = $module->__bootSequence;

		$this->_attachedModules[$moduleName] = $module;
		$this->_attachedModules[$moduleId] = $module;
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

		$requestQueue = array();
		foreach($value as $illustrator)
		{
			if(!is_array($illustrator))
				throw(new Exception("Error bootSequence structure definition"));

			if(!array_key_exists('module', $illustrator))
				throw(new Exception("Error bootSequence structure definition"));

			$moduleName = $illustrator['module'];

			$reuse = TRUE;
			if(array_key_exists('reuse', $illustrator))
			{
				if(!is_bool($illustrator['reuse']))
					throw(new Exception("Error bootSequence structure definition"));

				$reuse = $reuse && $illustrator['reuse'];
			}

			$request = NULL;
			if(array_key_exists('request', $illustrator))
				$request = $illustrator['request'];

			$moduleId = $this->_acquireModule($moduleName, $reuse)->id;
			PBLList::PUSH($this->_bootSequence, $moduleId, $moduleId);

			$requestQueue[] = $request;
		}

		PBLinkedList::HEAD($this->_bootSequence);
		PBLinkedList::NEXT($this->_bootSequence);

		foreach ($requestQueue as $request)
		{
			$handle = $this->_bootSequence->data;
			$this->_attachedModules[$handle]->prepare($request);

			PBLinkedList::NEXT($this->_bootSequence);
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