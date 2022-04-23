<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.2.0
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Site\Controller;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;

// require_once JPATH_COMPONENT.'/controller.php';
class PageController extends BaseController
{
	public function getModel($name = 'Page', $prefix = 'CGIsotopeModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}