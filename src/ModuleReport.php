<?php

namespace PrestaShop\ModuleAnalyzer;

class ModuleReport
{
    private $moduleName;
    private $registeredHooks    = [];
    private $availableHooks     = [];

    /**
     * Overrides contained in this module:
     * 	['filePath' => ['method1', 'method2', ...]]
     */
    private $overrides          = [];

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

        $op = new OverrideParser;
        $this->addOverride($op->parseOverride($contents));

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

    public function addOverride(array $override)
    {
        $this->overrides = array_merge($this->overrides, $override);
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

    public function getOverrides()
    {
        return $this->overrides;
    }

    public function __toString()
    {
        return sprintf(
            'Module `%1$s` has %2$d overrides and responds to %3$d hooks.',
            $this->moduleName,
            count($this->overrides),
            count($this->availableHooks)
        );
    }

    public function toArray()
    {
        return [
            'moduleName'        => $this->getModuleName(),
            'availableHooks'    => array_keys($this->getAvailableHooks()),
            'registeredHooks'   => array_keys($this->getRegisteredHooks()),
            'overrides'         => $this->getOverrides()
        ];
    }
}
