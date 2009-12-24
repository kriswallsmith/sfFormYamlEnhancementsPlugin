<?php

include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

$t = new lime_test(6);

class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
{
  public function loadWorker()
  {
    if (!class_exists('sfFormYamlEnhancementsWorker'))
    {
      $configHandler = new sfFormYamlEnhancementsConfigHander();
      $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

      $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
      file_put_contents($file, $code);

      require $file;
    }
  }
}

class CommentForm extends BaseForm
{
  public function configure()
  {
    $this->setWidget('body', new sfWidgetFormTextarea());
    $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
  }
}

$configuration = $configuration->getApplicationConfiguration(
  'frontend', 'test', true, null, $configuration->getEventDispatcher());
sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

$enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

// ->enhance()
$t->diag('->enhance()');

$form = new CommentForm();
$form->bind(array('body' => '+1'));

$enhancer->enhance($form);

$t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances labels');
$t->like($form['body']->render(), '/class="comment"/', '->enhance() enhances widgets');
$t->like($form['body']->renderError(), '/You haven\'t written enough/', '->enhance() enhances error messages');

$form = new CommentForm();
$form->bind();
$enhancer->enhance($form);
$t->like($form['body']->renderError(), '/A base required message/', '->enhance() considers inheritance');

class SpecialCommentForm extends CommentForm { }
$form = new SpecialCommentForm();
$form->bind();
$enhancer->enhance($form);
$t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() applies parent config');

$form = new BaseForm();
$form->embedForm('comment', new CommentForm());
$form->bind();
$enhancer->enhance($form);
$t->like($form['comment']['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances embedded forms');
