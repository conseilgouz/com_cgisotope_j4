<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 4.2.2
* Package			: CG ISotope
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
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