<?php
/**
* CG Isotope Component  - Joomla 4.x Component 
* Version			: 3.2.0
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;

class PageModel extends AdminModel {

    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);
    }
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_cgisotope.page', 'page', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()  {
		$data = Factory::getApplication()->getUserState('com_cgisotope.edit.item.data', array());
		if (empty($data)) $data = $this->getItem();
        // split general parameters
		$compl = new Registry($data->page_params);
		$data->intro = $compl['intro'];
		$data->middle = $compl['middle'];
		$data->bottom = $compl['bottom']; 
        $data->iso_entree = $compl['iso_entree'];
        $data->categories = $compl['categories'];
        $data->wl_categories = $compl['wl_categories'];
        $data->categories_k2 = $compl['categories_k2'];
        $data->introtext_limit = $compl['introtext_limit'];
        $data->hide_more = $compl['hide_more'];
        $data->introtext_leave_tags = $compl['introtext_leave_tags'];
        $data->blocklink = $compl['blocklink'];
        $data->introtext_img = $compl['introtext_img'];
        $data->introtext_img_link = $compl['introtext_img_link'];
        $data->titlelink = $compl['titlelink'];
        $data->readmore = $compl['readmore'];
        $data->introimg_maxwidth = $compl['introimg_maxwidth'];
        $data->introimg_maxheight = $compl['introimg_maxheight'];
        $data->iso_count = $compl['iso_count'];
        $data->limit_items = $compl['limit_items'];
        $data->pagination = $compl['pagination'];
        $data->infinite_btn = $compl['infinite_btn'];
        $data->page_count = $compl['page_count'];
        $data->page_order = $compl['page_order'];
        $data->language_filter= $compl['language_filter'];
        $data->cat_or_tag = $compl['cat_or_tag'];
        $data->tags = $compl['tags'];
        $data->default_cat = $compl['default_cat'];
        $data->default_cat_wl = $compl['default_cat_wl'];
        $data->default_tag = $compl['default_tag'];
        $data->tags_k2 = $compl['tags_k2'];
        $data->default_cat_k2 = $compl['default_cat_k2'];
        $data->default_tag_k2 = $compl['default_tag_k2'];
        $data->displayfields = $compl['displayfields'];
        $data->iso_layout = $compl['iso_layout'];
        $data->iso_nbcol = $compl['iso_nbcol'];
        $data->backgroundcolor = $compl['backgroundcolor'];
        $data->displaysort = $compl['displaysort'];
        $data->choixdate = $compl['choixdate'];
        $data->defaultdisplay = $compl['defaultdisplay'];
        $data->displayfiltertags = $compl['displayfiltertags'];
        $data->displayfiltercat = $compl['displayfiltercat'];
        $data->displayfiltersplitfields = $compl['displayfiltersplitfields'];
		$data->splitfieldscolumn = $compl['splitfieldscolumn'];
        $data->splitfieldstitle = $compl['splitfieldstitle'];
        $data->displayfilterfields = $compl['displayfilterfields'];
        $data->tagsmissinghidden = $compl['tagsmissinghidden'];
        $data->tagsfilterorder = $compl['tagsfilterorder'];
        $data->tagsfilterimg = $compl['tagsfilterimg'];
        $data->tagsfiltercount = $compl['tagsfiltercount'];
        $data->tagsfilterlink = $compl['tagsfilterlink'];
        $data->tagsfilterlinkcls = $compl['tagsfilterlinkcls'];
        $data->tagsfilterparent = $compl['tagsfilterparent'];
        $data->tagsfilterparentlabel = $compl['tagsfilterparentlabel'];
        $data->catfilteralias = $compl['catfilteralias'];
        $data->catsfilterimg = $compl['catsfilterimg'];
        $data->bootstrapbutton = $compl['bootstrapbutton'];
        $data->displaysearch = $compl['displaysearch'];
        $data->displayrange = $compl['displayrange'];
        $data->rangefields = $compl['rangefields'];
        $data->rangestep = $compl['rangestep'];
        $data->displayalpha = $compl['displayalpha'];
        $data->displayoffcanvas = $compl['displayoffcanvas'];
        $data->offcanvaspos = $compl['offcanvaspos'];
        $data->offcanvasbtnpos = $compl['offcanvasbtnpos'];
		// 3.0.8 compatibility 
		$anarray=array('leave','left','right');
		if (!$data->offcanvasbtnpos && in_array($data->offcanvaspos,$anarray)) {
			$data->offcanvasbtnpos = $data->offcanvaspos;
			$data->offcanvaspos = "start";
		}
		$data->displaycalendar = $compl['displaycalendar'];
		$data->calendarfields = $compl['calendarfields'];
        $data->empty = $compl['empty'];
        $data->cookieduration = $compl['cookieduration'];
		$data->btnsubtitle = $compl['btnsubtitle'];
		$data->btnnew = $compl['btnnew'];
		$data->new_limit = $compl['new_limit'];
		$data->formatsortdate = $compl['formatsortdate'];
		$data->formatotherdate = $compl['formatotherdate'];
        $data->perso = $compl['perso'];
		$data->css = $compl['css'];
        $data->customjs = $compl['customjs'];
        $data->btndate = $compl['btndate'];
        $data->btncat = $compl['btncat'];
        $data->btnalpha = $compl['btnalpha'];
        $data->btnvisit = $compl['btnvisit'];
        $data->btnrating = $compl['btnrating'];
        $data->btnrandom = $compl['btnrandom'];
        $data->btnfeature = $compl['btnfeature'];
        $data->btnid = $compl['btnid'];
        $data->layouts = $data->sections;
		return $data;
    }
    /**
     *  Method to validate form data.
     */
    public function validate($form, $data, $group = null)
    {
        $name = $data['name'];
        unset($data["name"]);

        return array(
            'name'   => $name,
            'params' => json_encode($data)
        );
    }
	
}
