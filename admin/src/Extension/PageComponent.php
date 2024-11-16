<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 2.3.3
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/

namespace ConseilGouz\Component\CGIsotope\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

class PageComponent extends MVCComponent implements RouterServiceInterface
{
	use RouterServiceTrait;
}
