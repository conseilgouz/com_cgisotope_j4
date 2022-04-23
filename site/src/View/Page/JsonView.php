<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.2.0
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
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
use Joomla\Component\Content\Site\Model\ArticleModel; // 4.0. compatibility
use Joomla\CMS\MVC\View\JsonView as BaseHtmlView;

class JsonView extends BaseHtmlView {

	function display($tpl = null)
	{
		$input = Factory::getApplication()->input;
		$articleId = $input->get('article');
		if ($input->get('entree') == 'k2') {
			echo json_encode(self::getArticleK2((int)$articleId)); 
		} else {
			echo json_encode(self::getArticle((int)$articleId)); 
		}
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
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
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
	//-----------------------One K2 article display------------------------------------------//
	static function getArticleK2($id) {
	 	PluginHelper::importPlugin('content');
        $componentParams = ComponentHelper::getParams('com_k2');
		jimport('joomla.filesystem.file');
		$application = Factory::getApplication();
		$limit = 1;
		$cid = NULL;
		$ordering = '';
		$limitstart = 0;

		$user = Factory::getUser();
		$aid = $user->get('aid');
		$db = Factory::getDbo();

		$jnow = Factory::getDate();
		$now = $jnow->toSql();
                
		$nullDate = $db->getNullDate();

		$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams 
			FROM #__k2_items as i 
			LEFT JOIN #__k2_categories c ON c.id = i.catid 
			WHERE i.published = 1 ";
		$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		$query .= " AND i.trash = 0 AND c.published = 1 ";
		$query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		$query .= " AND c.trash = 0 
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." ) 
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." ) 
				AND i.id={$id}";
		if ($application->getLanguageFilter())
				{
					$languageTag = Factory::getLanguage()->getTag();
					$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
				}
		$db->setQuery($query);
		$item = $db->loadObject();
		        // Image K2
	    if ($componentParams->get('imageTimestamp')) {
            $date = Factory::getDate($item->modified);
            $timestamp = '?t='.$date->toUnix();
        } else {
            $timestamp = '';
        } 
		$imageFilenamePrefix = md5("Image".$item->id);
		$imagePathPrefix = JUri::base(true).'/media/k2/items/cache/'.$imageFilenamePrefix;
		if (JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.$imageFilenamePrefix.'_Generic.jpg')) {
			$item->imageGeneric = $imagePathPrefix.'_Generic.jpg'.$timestamp;
			$item->imageXSmall  = $imagePathPrefix.'_XS.jpg'.$timestamp;
			$item->imageSmall   = $imagePathPrefix.'_S.jpg'.$timestamp;
			$item->imageMedium  = $imagePathPrefix.'_M.jpg'.$timestamp;
			$item->imageLarge   = $imagePathPrefix.'_L.jpg'.$timestamp;
			$item->imageXLarge  = $imagePathPrefix.'_XL.jpg'.$timestamp;
		}
        // Select the size to use
        $image = 'imageSmall';
		$item->image = $item->$image; 
		// Render the query results
		K2Model::addIncludePath(JPATH_SITE.'/components/com_k2/models');
		$model = K2Model::getInstance('Item', 'K2Model');
		$dispatcher = JDispatcher::getInstance();
	    JPluginHelper::importPlugin('content');
	    JPluginHelper::importPlugin('k2');
		
		$item->extra_fields = $model->getItemExtraFields($item->extra_fields, $item);
		// Plugin rendering in extra fields
		if (is_array($item->extra_fields)) {
		    foreach ($item->extra_fields as $key => $extraField) {
		        if ($extraField->type == 'textarea' || $extraField->type == 'textfield') {
		            $tmp = new \stdClass;
		            $tmp->text = $extraField->value;
                    $dispatcher->trigger('onPrepareContent', array(&$tmp, &$params, $limitstart));
	                $dispatcher->trigger('onK2PrepareContent', array(&$tmp, &$params, $limitstart));
		            $extraField->value = $tmp->text;
		        }
		    }
		}
		
		
		$arr[0] = $item;			
		return $arr;
	}
}