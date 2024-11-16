<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
namespace ConseilGouz\Component\CGIsotope\Site\View\Page;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticleModel;
use Joomla\CMS\MVC\View\JsonView as BaseHtmlView;

class JsonView extends BaseHtmlView {

	function display($tpl = null)
	{
		$input = Factory::getApplication()->input;
		$articleId = $input->get('article');
		echo json_encode(self::getArticle((int)$articleId)); 
	}
	//-----------------------One article display------------------------------------------//
	static function getArticle($id) {
		PluginHelper::importPlugin('content');
		$model   = new ArticleModel(array('ignore_request' => true));
        if ($model) { // Set application parameters in model
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$params= $appParams;
		$model->setState('params', $appParams);

		$model->setState('list.start', 0);
		$model->setState('list.limit', 1);
		// $model->setState('filter.published', 1);
		$model->setState('filter.featured', 'show');
		$model->setState('filter.category_id', array());

		// Access filter
		$access =ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getApplication()->getIdentity()->id);
		$model->setState('filter.access', $access);

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());
		// Ordering
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');

		$item = $model->getItem($id);

		$item->slug    = $item->id . ':' . $item->alias;
		$item->catslug = $item->catid . ':' . $item->category_alias;
		if ($access || in_array($item->access, $authorised))
		{
			// We know that user has the privilege to view the article
			$item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
		}
		else
		{
			$item->link = Route::_('index.php?option=com_users&view=login');
		}
		// appliquer les plugins "content"
		$item_cls = new \stdClass;
		$item_cls->text = $item->fulltext;
		$item_cls->id = $item->id;
		$item_cls->params = $params;
		
		Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_content.article', &$item_cls, &$item_cls->params, 0)); // Joomla 4.0
		$item->fulltext = 	$item_cls->text;	
		$scripts = [];
		if (count($app->getDocument()->_scripts) > 0) { // scripts
		    foreach($app->getDocument()->_scripts as $key=>$val) {
		        $scripts[]= $key;
		    }
		}
		$item->scripts = new \stdClass;
		$item->scripts = $scripts;
		$css = [];
		if (count($app->getDocument()->_styleSheets) > 0) { // scripts
		    foreach($app->getDocument()->_styleSheets as $key=>$val) {
		        $css[]= $key;
		    }
		}
		$item->css = new \stdClass;
		$item->css = $css;
		$arr[0] = $item;
        }
        else {
        	$arr = false;
        }
       
		return $arr;
	}
}