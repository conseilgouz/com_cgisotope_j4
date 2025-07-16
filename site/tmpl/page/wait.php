<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

$comfield	= 'media/com_cgisotope/';
$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('isowait', $comfield.'css/isotope.css');

?>
<div class="article-loading" style="height:100vh">
</div>
