<?php

class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
{
  public function execute($configFiles)
  {
    $forms = self::getConfiguration($configFiles);

    $code = array();
    $code[] = '<?php';
    $code[] = '// auto-generated by '.__CLASS__;
    $code[] = '// date: '.date('Y/m/d H:is');
    $code[] = 'class sfFormYamlEnhancementsWorker';
    $code[] = '{';
    $code[] = '  static public $enhancable = '.var_export(array_keys($forms), true).';';

    foreach ($forms as $class => $fields)
    {
      $code[] = '  static public function enhance'.$class.'(sfFormFieldSchema $fields)';
      $code[] = '  {';
      $code[] = '    '.$this->getEnhancerCode($fields);
      $code[] = '  }';
    }

    $code[] = '}';

    return implode(PHP_EOL, $code);
  }

  protected function getEnhancerCode($fields)
  {
    $code = array();
    foreach ($fields as $field => $config)
    {
      $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
      $code[] = '{';

      if (isset($config['label']))
      {
        $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);', var_export($field, true), var_export($config['label'], true));
      }

      if (isset($config['attributes']))
      {
        $code[] = sprintf('  $fields[%s]->getWidget()->setAttributes(array_merge(', var_export($field, true));
        $code[] = sprintf('    $fields[%s]->getWidget()->getAttributes(),', var_export($field, true));
        $code[] = '    '.var_export($config['attributes'], true);
        $code[] = '  ));';
      }

      if (isset($config['errors']))
      {
        $code[] = sprintf('  if ($error = $fields[%s]->getError())', var_export($field, true));
        $code[] = '  {';
        $code[] = '    $error->getValidator()->setMessages(array_merge(';
        $code[] = '      $error->getValidator()->getMessages(),';
        $code[] = '      '.var_export($config['errors'], true);
        $code[] = '    ));';
        $code[] = '  }';
      }

      $code[] = '}';
    }

    return implode(PHP_EOL.'    ', $code);
  }

  static public function getConfiguration(array $configFiles)
  {
    return self::applyInheritance(self::parseYamls($configFiles));
  }

  static public function applyInheritance($config)
  {
    $classes = array_keys($config);

    $merged = array();
    foreach ($classes as $class)
    {
      if (class_exists($class))
      {
        $merged[$class] = $config[$class];
        foreach (array_intersect(class_parents($class), $classes) as $parent)
        {
          $merged[$class] = sfToolkit::arrayDeepMerge($config[$parent], $merged[$class]);
        }
      }
    }

    return $merged;
  }
}