<?php

if (!defined('TEST_ROOT')) define('TEST_ROOT', dirname(__FILE__));

require_once TEST_ROOT . '/../vendor/autoload.php';
require_once TEST_ROOT . '/support/MaestranoTestHelper.php';
require_once TEST_ROOT . '/support/sso/SessionTestHelper.php';
require_once TEST_ROOT . '/support/saml/SamlTestHelper.php';
require_once TEST_ROOT . '/support/stubs/SamlMnoRespStub.php';
require_once TEST_ROOT . '/support/stubs/MnoHttpClientStub.php';

date_default_timezone_set('UTC');

