<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Version			: 3.0.12
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Router\Route;

class ImportController extends FormController
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_CGISOTOPE_IMPORT';

    public function add($key = null, $urlVar = null) 
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $app = Factory::getApplication();
        $input = $app->input;
		$pks = $input->post->get('cid', array(), 'array');
        $db    = Factory::getDbo();
		foreach ($pks as $id)	{
            $result = $db->setQuery(
                $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('id') . ' = ' . (int)$id)
            )->loadAssocList();
            if (count($result) != 1) {
                $this->setMessage(Text::sprintf('CG_ISO_MODULE_SELECT_ERROR', $id), 'warning');
                $this->setRedirect(Route::_('index.php?option=com_cgisotope&view=import', false));
                return false;
            }
            $data = new \stdClass;
            $data->id= 0;
            $data->title = $this->check_title($result[0]['title']);
            $data->state = $result[0]['published'];
            $data->language = $result[0]['language'];            
            $page_params = [];
            $mod_params = json_decode($result[0]['params']);
            $page_params['iso_entree'] = $mod_params->iso_entree;
            $page_params['wl_categories'] = $mod_params->wl_categories;
            $page_params['categories_k2'] = $mod_params->categories_k2;
            $page_params['introtext_limit'] = $mod_params->introtext_limit;
            $page_params['introtext_leave_tags'] = $mod_params->introtext_leave_tags;
            $page_params['introtext_img'] = $mod_params->introtext_img;
            $page_params['introtext_img_link'] = $mod_params->introtext_img_link;
            $page_params['titlelink'] = $mod_params->titlelink;
            $page_params['readmore'] = $mod_params->readmore;
            $page_params['introimg_maxwidth'] = $mod_params->introimg_maxwidth;
            $page_params['introimg_maxheight'] = $mod_params->introimg_maxheight;
            $page_params['iso_count'] = $mod_params->iso_count;
            $page_params['limit_items'] = $mod_params->limit_items;
            $page_params['pagination'] = $mod_params->pagination;
            $page_params['infinite_btn'] = $mod_params->infinite_btn;
            $page_params['page_count'] = $mod_params->page_count;
            $page_params['page_order'] = $mod_params->page_order;
            $page_params['cat_or_tag'] = $mod_params->cat_or_tag;
            $page_params['tags'] = $mod_params->tags;
            $page_params['default_cat'] = $mod_params->default_cat;
            $page_params['default_cat_wl'] = $mod_params->default_cat_wl;
            $page_params['default_tag'] = $mod_params->default_tag;
            $page_params['tags_k2'] = $mod_params->tags_k2;
            $page_params['displayfields'] = $mod_params->displayfields;
            $page_params['iso_layout'] = $mod_params->iso_layout;
            $page_params['iso_nbcol'] = $mod_params->iso_nbcol;
            $page_params['backgroundcolor'] = $mod_params->backgroundcolor;
            $page_params['displaysort'] = $mod_params->displaysort;
            $page_params['choixdate'] = $mod_params->choixdate;
            $page_params['defaultdisplay'] = $mod_params->defaultdisplay;
            // $page_params['displayfiltercattags'] = $mod_params->displayfiltercattags;
            $page_params['displayfiltercat'] = $mod_params->displayfiltercat;
            $page_params['displayfiltertags'] = $mod_params->displayfilter;
            $page_params['displayfiltersplitfields'] = $mod_params->displayfiltersplitfields;
            $page_params['splitfieldscolumn'] = $mod_params->splitfieldscolumn;
            $page_params['splitfieldstitle'] = $mod_params->splitfieldstitle;
            $page_params['displayfilterfields'] = $mod_params->displayfilterfields;
            $page_params['tagsfilterorder'] = $mod_params->tagsfilterorder;
            $page_params['tagsfilterimg'] = $mod_params->tagsfilterimg;
            $page_params['tagsfiltercount'] = $mod_params->tagsfiltercount;
            $page_params['tagsfilterlink'] = $mod_params->tagsfilterlink;
            $page_params['catfilteralias'] = $mod_params->catfilteralias;
            $page_params['catsfilterimg'] = $mod_params->catsfilterimg;
            $page_params['bootstrapbutton'] = $mod_params->bootstrapbutton;
            $page_params['displaysearch'] = $mod_params->displaysearch;

/*-----RANGE ---*/
//            $page_params['displayrange'] = $mod_params->displayrange;
//            $page_params['rangefields'] = $mod_params->rangefields;
//            $page_params['rangestep'] = $mod_params->rangestep;
/*---------------*/
            $page_params['css'] = $mod_params->css;
            $page_params['btndate'] = $mod_params->btndate;
            $page_params['btncat'] = $mod_params->btncat;
            $page_params['btnalpha'] = $mod_params->btnalpha;
            $page_params['btnvisit'] = $mod_params->btnvisit;
            $page_params['btnrating'] = $mod_params->btnrating;
            $page_params['btnid'] = $mod_params->btnid;
            $page_params['perso'] = $mod_params->perso;
            $page_params['intro'] = '';
            $page_params['customjs'] = '';
            $data->page_params =  json_encode($page_params);
            $data->sections = json_encode($mod_params->layouts);
            $data->fieldslinks = json_encode($mod_params->fieldslinks);
            $ret = $db->insertObject('#__cgisotope_page', $data,'id');
            if (!$ret) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $ret), 'warning');
                $this->setRedirect(Route::_('index.php?option=com_cgisotope&view=import', false));
                return false;
            }
        }
        $this->setMessage(Text::sprintf('CG_ISO_MODULE_IMPORTED', count($pks)), 'notice');
        $this->setRedirect(Route::_('index.php?option=com_cgisotope&view=import', false));
        return false;
        }
	function check_title($title) {
        $db    = Factory::getDbo();
        do {
			$result = $db->setQuery(
                $db->getQuery(true)
                ->select('count(*)')
                ->from($db->quoteName('#__cgisotope_page'))
                ->where($db->quoteName('title') . ' like ' . $db->quote($title) .' AND state in (0,1)')
            )->loadResult();
			if ($result > 0) $title = StringHelper::increment($title);
		}
		while ($result > 0);
		return $title;
	}

}