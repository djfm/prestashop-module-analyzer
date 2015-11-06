<?php

namespace PrestaShop\ModuleAnalyzer;

class ModuleReport
{
    private $moduleName;
    private $registeredHooks    = [];
    private $availableHooks     = [];

    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function parsePhpFile($filename, $contents)
    {
        $hp = new HookParser;
        array_map([$this, 'addRegisteredHook'], $hp->parseRegisteredHooks($contents));
        array_map([$this, 'addAvailableHook'], $hp->parseAvailableHooks($contents));
        return $this;
    }

    public function addRegisteredHook($hookName)
    {
        $this->registeredHooks[$hookName] = $hookName;
        return $this;
    }

    public function addAvailableHook($hookName)
    {
        $this->availableHooks[$hookName] = $hookName;
        return $this;
    }

    public function getAvailableHooks()
    {
        return $this->availableHooks;
    }

    public function getRegisteredHooks()
    {
        return $this->registeredHooks;
    }
}
