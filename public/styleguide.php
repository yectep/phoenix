<?php

/**
 * CSS style definition guide as POC
 *
 * @author	Yectep Studios <info@yectep.hk>
 * @version	20707
 * @package Plume
 */


define('PTP',   '../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_NEWS',      true);
define('PHX_UX',        true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// Set page switch variables
$h['title'] = 'Style Guide';
$n['styleguide'] = 'active';

// Breadcrumb


// Include header section
echo UX::makeHead($h, $n);

// Page info
echo UX::makeBreadcrumb(array(	'Extras'		=> 'javascript:;',
								'Style Guide'	=> '/styleguide.php'));
echo UX::grabPage('dev/styleguide', null, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>