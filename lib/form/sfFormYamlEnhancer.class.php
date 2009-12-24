<?php

class sfFormYamlEnhancer
{
  protected
    $configCache = null;

  public function __construct(sfConfigCache $configCache)
  {
    $this->configCache = $configCache;
    $this->configCache->registerConfigHandler('config/forms.yml', 'sfFormYamlEnhancementsConfigHander');
  }

  public function connect(sfEventDispatcher $dispatcher)
  {
    $dispatcher->connect('template.filter_parameters', array($this, 'filterParameters'));
  }

  public function filterParameters(sfEvent $event, $parameters)
  {
    foreach ($parameters as $name => $parameter)
    {
      if ($parameter instanceof sfForm)
      {
        $this->enhance($parameter);
      }
    }

    return $parameters;
  }

  public function enhance(sfForm $form)
  {
    $this->loadWorker();
    $this->doEnhance($form->getFormFieldSchema(), $form);
  }

  public function loadWorker()
  {
    require_once $this->configCache->checkConfig('config/forms.yml');
  }

  protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
  {
    if ($enhancer = $this->getEnhancer(get_class($form)))
    {
      call_user_func($enhancer, $fieldSchema);
    }

    foreach ($form->getEmbeddedForms() as $name => $form)
    {
      if (isset($fieldSchema[$name]))
      {
        $this->doEnhance($fieldSchema[$name], $form);
      }
    }
  }

  public function getEnhancer($class)
  {
    if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
    {
      return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
    }
    else if ($overlap = array_intersect(class_parents($class), sfFormYamlEnhancementsWorker::$enhancable))
    {
      return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
    }
  }
}
