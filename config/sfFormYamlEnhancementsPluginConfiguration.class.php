<?php

class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
      $enhancer->connect($this->dispatcher);
    }
  }
}
