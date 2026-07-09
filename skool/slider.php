<?php
/**
 * Legacy route forwarder.
 * slider.php now points to configuration.php.
 */

require_once('../config.php');
require_once('inc.session-create.php');

$queryString = $_SERVER['QUERY_STRING'] ?? '';
redirect('configuration.php' . ($queryString !== '' ? ('?' . $queryString) : ''));
exit;
