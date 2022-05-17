<?

global $USER;
$this->setFrameMode(true);
$this->addExternalCss($templateFolder . '/style.css');
$this->addExternalJs($templateFolder . '/scripts.js');
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$templatePath = '/local/components/UW/calc/templates/.default/images/';
