<?php
/**
* CG Isotope Component  - Joomla 4.x/5x Component
* Version			: 3.3.4
* Package			: CG ISotope
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\LanguageHelper;

$comfield	= 'media/com_cgisotope/';
$app = Factory::getApplication();

$com_id = $app->input->getInt('Itemid');
$document = $app->getDocument();

$this->iso_params = CGHelper::getParams($this->page, $this->getModel());

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle('iso', $comfield.'css/isotope.css');
// $wa->registerAndUseStyle('up',$comfield.'css/up.css');
$wa->registerAndUseStyle('rslider', $comfield.'css/rSlider.min.css');
if ($this->iso_params->get('css')) {
    $wa->addInlineStyle($this->iso_params->get('css'));
}
if ($this->iso_params->get("pagination", "false") == 'infinite') {
    $wa->registerAndUseScript('infinite', $comfield.'js/infinite-scroll.min.js');
} else {
    $wa->registerAndUseScript('imgload', $comfield.'js/imagesloaded.min.js');
}
$wa->registerAndUseScript('isotope', $comfield.'js/isotope.min.js');
$wa->registerAndUseScript('packery', $comfield.'js/packery-mode.min.js');
$wa->registerAndUseScript('rslider', $comfield.'js/rSlider.min.js');
if ($this->iso_params->get('customjs')) {
    $wa->addInlineScript($this->iso_params->get('customjs'));
}

$this->iso_layout = $this->iso_params->get('iso_layout', 'fitRows');
$this->iso_nbcol = $this->iso_params->get('iso_nbcol', 2);
$this->imgmaxwidth =  $this->iso_params->get('introimg_maxwidth', '0');
$this->imgmaxheight =  $this->iso_params->get('introimg_maxheight', '0');

if (($this->iso_layout == "masonry") || ($this->iso_layout == "fitRows") || ($this->iso_layout == "packery")) {
    $width = (100 / $this->iso_nbcol) - 2;
    $wa->addInlineStyle('#isotope-main-'.$com_id.' .isotope_item{ width:'.$width.'%}');
}
if ($this->iso_layout == "vertical") {
    $wa->addInlineStyle('#isotope-main-'.$com_id.' .isotope_item{ width:100%}');
}
if ($this->imgmaxwidth) {
    $wa->addInlineStyle('#isotope-main-'.$com_id.' .isotope_item img{ max-width:'.$this->imgmaxwidth.'px}');
}
if ($this->imgmaxheight) {
    $wa->addInlineStyle('#isotope-main-'.$com_id.' .isotope_item img{ max-height:'.$this->imgmaxheight.'px}');
}
if ((bool) $app->getConfig()->get('debug')) {
    $document->addScript(''.URI::base(true).'/media/com_cgisotope/js/init.js');
} else {
    $wa->registerAndUseScript('cgisotope', $comfield.'js/init.min.js');
}
$user = $app->getIdentity();
$userId = $user->id;

$this->start = 0;
if ($this->iso_params->get("pagination", "false") != 'false') {
    $this->start = $app->input->getInt('limitstart', 0);
}
$this->language_filter = $this->iso_params->get('language_filter', 'false');
if ($this->language_filter != 'false') { // language filter
    $this->languagelist = LanguageHelper::getLanguages();
}
$this->language = $this->iso_params->get('language', '*');
$this->iso_entree = $this->iso_params->get('iso_entree', 'webLinks');
$this->article_cat_tag = $this->iso_params->get('cat_or_tag', $this->iso_entree == "webLinks" ? 'cat' : 'tags');
$this->tags_list = $this->iso_params->get('tags', array());
// check authorised tags
$authorised = Access::getAuthorisedViewLevels($userId);
foreach ($this->tags_list as $key => $atag) {
    if (!CGHelper::getTagAccess($atag, $authorised)) {
        unset($this->tags_list[$key]); // not authorized : remove it
    }
}
$this->fields_list = $this->iso_params->get('displayfields');
$this->iso_pagination = $this->iso_params->get('pagination', 'false');
$this->page_count = $this->iso_params->get('page_count', '0');
$this->iso_limit = $this->iso_params->get('iso_limit', 'all');
$this->displayrange =  $this->iso_params->get('displayrange', 'false');
$this->rangefields =   $this->iso_params->get('rangefields', '');
$this->rangestep =   $this->iso_params->get('rangestep', '0');
$this->calendarfields =   array();
$this->displayalpha =  $this->iso_params->get('displayalpha', 'false');
$this->displayoffcanvas =  $this->iso_params->get('displayoffcanvas', 'text');
$this->offcanvaspos = $this->iso_params->get('offcanvaspos', 'start');
$this->offcanvasbtnpos = "leave";
if ($this->displayoffcanvas == "hamburger") {
    $this->offcanvasbtnpos =  $this->iso_params->get('offcanvasbtnpos', 'leave');
}
$this->displaybootstrap = $this->iso_params->get('bootstrapbutton', 'false');
$button_bootstrap = "isotope_button";
if ($this->displaybootstrap == 'true') {
    HTMLHelper::_('bootstrap.button', '.selector');
    $button_bootstrap = "btn btn-sm ";
}
$this->minrange = '';
$this->maxrange = '';
$this->rangetitle =  '';
$this->rangelabel =  '';
$this->rangedesc =  '';
$this->tags = array();
$this->tags_alias = array();
$this->tags_image = array();
$this->tags_note = array();
$this->tags_link = array();
$this->tags_count = array();
$this->tags_parent = array();
$this->tags_parent_alias = array();
$this->fields = array();
$this->cats_lib = array();
$this->cats_alias = array();
$this->cats_params = array();
$this->cats_note = array();
$this->article_fields = array();
$this->article_fields_names = array();
$this->pagination = array();
$this->alpha = array();
$this->calendar = array();

if ($this->iso_entree == "webLinks") {
    $this->categories = $this->iso_params->get('wl_categories');
    $weblinks_params = ComponentHelper::getParams('com_weblinks');
    $this->list = CGHelper::getWebLinks($this->iso_params, $weblinks_params, $this);
    if (!$this->list) {
        return false;
    } // on a eu une erreur: on sort
} else {
    $this->categories = $this->iso_params->get('categories');
    if (is_null($this->categories)) {
        $res = CGHelper::getAllCategories($this->iso_params);
        $this->categories = array();
        foreach ($res as $catid) {
            if ($catid->count > 0) {
                $this->categories[] = $catid->id;
            }
        }
    }
    $this->article_tags = array();
    if ($this->iso_params->get("pagination", "false") != 'false') {
        $this->limit = (int) $this->iso_params->get("page_count", 0);
        $this->order =  $this->iso_params->get("page_order", "a.ordering");
    } else {
        $this->limit = (int) $this->iso_params->get('iso_count', 0);
        $this->order = "a.ordering";
    }
    $this->list[] = CGHelper::getItems($this->categories, $this->iso_params, $this, $this->pagination);
}
// pagination : check tags_list to add missing tags in the list
if (sizeof($this->tags_list) && ($this->iso_params->get("pagination", "false") != 'false')) {
    $authorised = Access::getAuthorisedViewLevels($userId);
    $missings = CGHelper::getMissingTags($this->tags_list, $authorised);
    foreach ($missings as $tag) {
        if (!in_array($tag->tag, $this->tags)) {
            $this->tags[] = $tag->tag;
            $this->tags_alias[$tag->tag] = $tag->alias;
            $this->tags_image[$tag->alias] = $tag->images;
            $this->tags_note[$tag->alias] = $tag->note;
            $this->tags_link[$tag->alias] = CGHelper::getTagLink($tag);
            $this->tags_parent[$tag->alias] = $tag->parent_title;
            $this->tags_parent_alias[$tag->alias] = $tag->parent_alias;
        }
        $this->tags_count[$tag->alias]++;
    }
}
//-------------------- parameters to send to JS ---------------------------------------//
$defaultdisplay = $this->iso_params->get('defaultdisplay', 'date_desc');
$sortBy = "";
$sortAscending = "";
if ($defaultdisplay == "date_asc") {
    $sortBy = "date";
    $sortAscending = "true";
}
if ($defaultdisplay == "date_desc") {
    $sortBy = "date";
    $sortAscending = "false";
}
if ($defaultdisplay == "cat_asc") {
    $sortBy = "category";
    $sortAscending = "true";
}
if ($defaultdisplay == "cat_desc") {
    $sortBy = "category";
    $sortAscending = "false";
}
if ($defaultdisplay == "alpha_asc") {
    $sortBy = "title";
    $sortAscending = "true";
}
if ($defaultdisplay == "alpha_desc") {
    $sortBy = "title";
    $sortAscending = "false";
}
if ($defaultdisplay == "click_asc") {
    $sortBy = "click";
    $sortAscending = "true";
}
if ($defaultdisplay == "click_desc") {
    $sortBy = "click";
    $sortAscending = "false";
}
if ($defaultdisplay == "rating_asc") {
    $sortBy = "rating";
    $sortAscending = "true";
}
if ($defaultdisplay == "rating_desc") {
    $sortBy = "rating";
    $sortAscending = "false";
}
if ($defaultdisplay == "id_asc") {
    $sortBy = "id";
    $sortAscending = "true";
}
if ($defaultdisplay == "id_desc") {
    $sortBy = "id";
    $sortAscending = "false";
}
if ($defaultdisplay == "random") {
    $sortBy = "random";
    $sortAscending = "false";
}

if ($this->iso_params->get('btnfeature', 'false') != "false") { // featured always first
    $sortBy = 'featured,'.$sortBy;
}
if ($this->iso_entree == "webLinks") {
    $this->default_cat = $this->iso_params->get('default_cat_wl', '');
    if (($this->default_cat != "") && ($this->default_cat != "none")) {
        $this->default_cat = $this->cats_alias[$this->iso_params->get('default_cat_wl')];
    }
    $this->default_tag = $this->iso_params->get('default_tag', '');
    if (($this->default_tag != "") && ($this->default_tag != "none")) {
        $onetag = CGHelper::getTagTitle($this->default_tag);
        $this->default_tag = $onetag[0]->alias;
    }
} else {
    $this->default_cat = $this->iso_params->get('default_cat', '');
    if (($this->default_cat != "") && ($this->default_cat != "none")) {
        $this->default_cat = $this->cats_alias[$this->iso_params->get('default_cat')];
    }
    $this->default_tag = $this->iso_params->get('default_tag', '');
    if (($this->default_tag != "") && ($this->default_tag != "none")) {
        $onetag = CGHelper::getTagTitle($this->default_tag);
        $this->default_tag = $onetag[0]->alias;
    }
}

if (($this->displayrange == "true") && ($this->rangestep == "auto")) {
    $step = ((int)$this->maxrange - (int)$this->minrange) / 5 ; // PHP 8
    if ($step < 1) {
        $step = 1;
    }
    $this->rangestep = $step;
}

$this->default_field = "";
if (($this->article_cat_tag == "fields") || ($this->article_cat_tag == "catfields") || ($this->article_cat_tag == "tagsfields") || ($this->article_cat_tag == "cattagsfields")) {
    $this->splitfields = $this->iso_params->get('displayfiltersplitfields', 'false');
    $this->displayfilterfields =  $this->iso_params->get('displayfilterfields', 'button');
    $this->displayfiltercat = $this->iso_params->get('displayfiltercat', 'button');
    $this->displayfiltertags = $this->iso_params->get('displayfiltertags', 'button');
    $this->searchmultiex = "false";
    if (($this->displayfilterfields == "multiex") || ($this->displayfilterfields == "listex")) {
        $this->searchmultiex = "true";
    }
    $document->addScriptOptions(
        'cg_isotope_'.$com_id,
        array('entree' => $this->iso_entree,'article_cat_tag' => $this->article_cat_tag,
              'default_cat' => $this->default_cat,
              'default_tag' => $this->default_tag,
              'default_field' => $this->default_field,
              'layout' => $this->iso_layout,'nbcol' => $this->iso_nbcol,
              'background' => $this->iso_params->get("backgroundcolor", "#eee"),
              'imgmaxwidth' => $this->iso_params->get('introimg_maxwidth', '0'),
              'imgmaxheight' => $this->iso_params->get('introimg_maxheight', '0'),
              'sortby' => $sortBy, 'ascending' => $sortAscending,
              'searchmultiex' => $this->searchmultiex, 'liball' => Text::_('CG_ISO_LIBALL'),
              'language_filter' => $this->language_filter,
              'displayfilterfields' =>  $this->displayfilterfields,
              'displayfiltercat' => $this->displayfiltercat,'displayfiltertags' => $this->displayfiltertags,
              'displayrange' => $this->displayrange,'rangestep' => $this->rangestep, 'minrange' => $this->minrange,'maxrange' => $this->maxrange,
              'displayalpha' => $this->displayalpha,'limit_items' => $this->iso_params->get('limit_items', '0'),
              'libmore' => Text::_('CG_ISO_LIBMORE'), 'libless' => Text::_('CG_ISO_LIBLESS'),'readmore' => $this->iso_params->get("readmore", "false"),
              'empty' => $this->iso_params->get("empty", "false"),
              'pagination' => $this->iso_pagination,'page_count' => $this->page_count,'infinite_btn' => $this->iso_params->get("infinite_btn", "false"),
              'button_bootstrap' => $button_bootstrap,'layouts' => json_decode($this->iso_params->get('layouts')),
              'cookieduration' => $this->iso_params->get("cookieduration", "0"))
    );

} else {
    $this->displayfiltercat = $this->iso_params->get('displayfiltercat', 'button');
    $this->displayfiltertags = $this->iso_params->get('displayfiltertags', 'button');

    $this->searchmultiex = "false";
    if (($this->displayfiltercat == "multiex") || ($this->displayfiltercat == "listex") ||
        ($this->displayfiltertags == "multiex") || ($this->displayfiltertags == "listex")) {
        $this->searchmultiex = "true";
    }
    $document->addScriptOptions(
        'cg_isotope_'.$com_id,
        array('entree' => $this->iso_entree,'article_cat_tag' => $this->article_cat_tag,
              'default_cat' => $this->default_cat,
              'default_tag' => $this->default_tag,
              'layout' => $this->iso_layout,'nbcol' => $this->iso_nbcol,
              'background' => $this->iso_params->get("backgroundcolor", "#eee"),
              'imgmaxwidth' => $this->iso_params->get('introimg_maxwidth', '0'),
              'imgmaxheight' => $this->iso_params->get('introimg_maxheight', '0'),
              'sortby' => $sortBy, 'ascending' => $sortAscending,
              'searchmultiex' => $this->searchmultiex, 'liball' => Text::_('CG_ISO_LIBALL'),
              'language_filter' => $this->language_filter,
              'displayfiltertags' => $this->displayfiltertags, 'displayfiltercat' => $this->displayfiltercat,
              'displayrange' => $this->displayrange,'rangestep' => $this->rangestep, 'minrange' => $this->minrange,'maxrange' => $this->maxrange,
              'displayalpha' => $this->displayalpha,'limit_items' => $this->iso_params->get('limit_items', '0'),
              'libmore' => Text::_('CG_ISO_LIBMORE'), 'libless' => Text::_('CG_ISO_LIBLESS'),'readmore' => $this->iso_params->get("readmore", "false"),
              'empty' => $this->iso_params->get("empty", "false"),
              'pagination' => $this->iso_pagination,'page_count' => $this->page_count,'infinite_btn' => $this->iso_params->get("infinite_btn", "false"),
              'button_bootstrap' => $button_bootstrap,'layouts' => json_decode($this->iso_params->get('layouts')),
              'cookieduration' => $this->iso_params->get("cookieduration", "0"))
    );
}

PluginHelper::importPlugin('cgisotope');
$app->triggerEvent('onCGIsotopeBefore', array('com_cgisotope.article', $this));
?>
<?php
if (($this->article_cat_tag == "fields") || ($this->article_cat_tag == "catfields") || ($this->article_cat_tag == "tagsfields") || ($this->article_cat_tag == "cattagsfields")) {
    echo $this->loadTemplate('fields');
} else {
    echo $this->loadTemplate('cat_tags');
}
?>