<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
namespace ConseilGouz\Component\CGIsotope\Site\Controller;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
class PageController extends BaseController {
	public function getModel($name = 'Page', $prefix = 'CGIsotopeModel',$config = []) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}