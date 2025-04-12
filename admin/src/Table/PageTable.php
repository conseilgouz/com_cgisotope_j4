<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component 
* Package			: CG Isotope
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Table;

\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

class PageTable extends Table implements VersionableTableInterface
{
	/**
	 * An array of key names to be json encoded in the bind function
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $_jsonEncode = ['params', 'metadata', 'urls', 'images'];

	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  A database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cgisotope_page', 'id', $db);
		$this->created = Factory::getDate()->toSql();
	}


	function check()
	{
		jimport('joomla.filter.output');
		return true;
	}
	function store($updateNulls = true)
	{
        $db	= Factory::getContainer()->get(DatabaseInterface::class);
        $table = $this->_tbl;
        $key   = empty($this->id) ? $key : $this->id;

        // Check if key exists
        $result = $db->setQuery(
            $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName($this->_tbl))
                ->where($db->quoteName('id') . ' = ' . $db->quote($key))
        )->loadResult();

        $exists = $result > 0 ? true : false;

        // Prepare object to be saved
        $data = new \stdClass();
        $data->id   = $key;
        $data->title = $this->title;
        $data->sections = $this->sections;
		$data->fieldslinks = $this->fieldslinks;
        $input = Factory::getApplication()->input;
        $task = $input->get('task');
        if ( ($task == "save") || ($task == 'apply') ){
            $compl = $input->getVar('jform', array(), 'post', 'array');
            $page_params = [];
            $page_params['iso_entree'] = $compl['iso_entree'];
            $page_params['categories'] = $compl['categories'];
            $page_params['wl_categories'] = $compl['wl_categories'];
            $page_params['categories_k2'] = $compl['categories_k2'];
            $page_params['introtext_limit'] = $compl['introtext_limit'];
            $page_params['hide_more'] = $compl['hide_more'];
            $page_params['introtext_leave_tags'] = $compl['introtext_leave_tags'];
            $page_params['blocklink'] = $compl['blocklink'];
            $page_params['introtext_img'] = $compl['introtext_img'];
            $page_params['introtext_img_link'] = $compl['introtext_img_link'];
            $page_params['titlelink'] = $compl['titlelink'];
            $page_params['readmore'] = $compl['readmore'];
            $page_params['readmoretext'] = $compl['readmoretext'];
            $page_params['introimg_maxwidth'] = $compl['introimg_maxwidth'];
            $page_params['introimg_maxheight'] = $compl['introimg_maxheight'];
            $page_params['iso_count'] = $compl['iso_count'];
            $page_params['limit_items'] = $compl['limit_items'];
            $page_params['pagination'] = $compl['pagination'];
            $page_params['infinite_btn'] = $compl['infinite_btn'];
            $page_params['page_count'] = $compl['page_count'];
            $page_params['page_order'] = $compl['page_order'];
            $page_params['language_filter']= $compl['language_filter'];
            $page_params['cat_or_tag'] = $compl['cat_or_tag'];
            $page_params['tags'] = $compl['tags'];
            $page_params['default_cat'] = $compl['default_cat'];
            $page_params['default_cat_wl'] = $compl['default_cat_wl'];
            $page_params['default_tag'] = $compl['default_tag'];
            $page_params['tags_k2'] = $compl['tags_k2'];
            $page_params['default_cat_k2'] = $compl['default_cat_k2'];
            $page_params['default_tag_k2'] = $compl['default_tag_k2'];
            $page_params['displayfields'] = $compl['displayfields'];
            $page_params['iso_layout'] = $compl['iso_layout'];
            $page_params['iso_nbcol'] = $compl['iso_nbcol'];
            $page_params['backgroundcolor'] = $compl['backgroundcolor'];
            $page_params['displaysort'] = $compl['displaysort'];
            $page_params['choixdate'] = $compl['choixdate'];
            $page_params['defaultdisplay'] = $compl['defaultdisplay'];
            $page_params['displayfiltertags'] = $compl['displayfiltertags'];
            $page_params['displayfiltercat'] = $compl['displayfiltercat'];
        // $page_params['displayfilter'] = $compl['displayfilter'];
            $page_params['displayfiltersplitfields'] = $compl['displayfiltersplitfields'];
            $page_params['splitfieldscolumn'] = $compl['splitfieldscolumn'];
            $page_params['splitfieldstitle'] = $compl['splitfieldstitle'];
            $page_params['fieldsfiltercount'] = $compl['fieldsfiltercount'];
            $page_params['displayfilterfields'] = $compl['displayfilterfields'];
            $page_params['tagsmissinghidden'] = $compl['tagsmissinghidden'];
            $page_params['tagsfilterorder'] = $compl['tagsfilterorder'];
            $page_params['tagsfilterimg'] = $compl['tagsfilterimg'];
            $page_params['tagsfiltercount'] = $compl['tagsfiltercount'];
            $page_params['tagsfilterlink'] = $compl['tagsfilterlink'];
            $page_params['tagsfilterlinkcls'] = $compl['tagsfilterlinkcls'];
            $page_params['tagsfilterparent'] = $compl['tagsfilterparent'];
            $page_params['tagsfilterparentlabel'] = $compl['tagsfilterparentlabel'];
            $page_params['catfilteralias'] = $compl['catfilteralias'];
            $page_params['catsfilterimg'] = $compl['catsfilterimg'];
            $page_params['catsfiltercount'] = $compl['catsfiltercount'];
            $page_params['bootstrapbutton'] = $compl['bootstrapbutton'];
            $page_params['displaysearch'] = $compl['displaysearch'];
            $page_params['displayrange'] = $compl['displayrange'];
            $page_params['rangefields'] = $compl['rangefields'];
            $page_params['rangestep'] = $compl['rangestep'];
            $page_params['displayalpha'] = $compl['displayalpha'];
            $page_params['displayoffcanvas'] = $compl['displayoffcanvas'];
            $page_params['offcanvaspos'] = $compl['offcanvaspos'];
            $page_params['offcanvasbtnpos'] = $compl['offcanvasbtnpos'];
            $page_params['displaycalendar'] = $compl['displaycalendar'];
            $page_params['calendarfields'] = $compl['calendarfields'];
            $page_params['empty'] = $compl['empty'];
            $page_params['cookieduration'] = $compl['cookieduration'];
            $page_params['css'] = $compl['css'];
            $page_params['btnsubtitle'] = $compl['btnsubtitle'];
            $page_params['btnnew'] = $compl['btnnew'];
            $page_params['new_limit'] = $compl['new_limit'];
            $page_params['formatsortdate'] = $compl['formatsortdate'];
            $page_params['formatotherdate'] = $compl['formatotherdate'];
            $page_params['bracket'] = $compl['bracket'];
            $page_params['btndate'] = $compl['btndate'];
            $page_params['btncat'] = $compl['btncat'];
            $page_params['btnalpha'] = $compl['btnalpha'];
            $page_params['btnvisit'] = $compl['btnvisit'];
            $page_params['btnrating'] = $compl['btnrating'];
            $page_params['btnid'] = $compl['btnid'];
            $page_params['btnrandom'] = $compl['btnrandom'];
            $page_params['btnfeature'] = $compl['btnfeature'];
	// texteara with special html tags to keep	
            $perso = $input->getRaw('jform', 'perso', 'post', 'array');		
            $page_params['perso'] = $perso['perso'];
            $page_params['intro'] = $perso['intro'];
            $page_params['middle'] = $perso['middle'];
            $page_params['bottom'] = $perso['bottom']; 
	// texteara with special html tags to keep	
            $custom = $input->getRaw('jform', 'customjs', 'post', 'array');		
            $page_params['customjs'] = $custom['customjs'];
            $data->page_params =  json_encode($page_params);
            $data->sections = json_encode($compl['layouts']);
            $data->fieldslinks = json_encode($compl['fieldslinks']);
        }
        $data->state = $this->state;
		$data->language = $this->language;
		if ($task == "save") { // save & close : reset checkout
		  $data->checked_out = 0;
		  $data->checked_out_time = null;
		}
        if ($exists) { // update
            return $db->updateObject($table, $data, 'id');
        }
		// insert a new object
		$ret = $db->insertObject($table, $data,'id');
		$this->id = $data->id;
		return $ret;
	}
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;
		$db = $this->getDbo();
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			else {
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}
		$table = Table::getInstance('PageTable', __NAMESPACE__ . '\\', array('dbo' => $db));
		foreach ($pks as $pk)
		{
			if(!$table->load($pk))
			{
				$this->setError($table->getError());
			}
			if($table->checked_out==0 || $table->checked_out==$userId)
			{
				$table->state = $state;
				$table->checked_out=0;
				$table->checked_out_time=0;
				$table->check();
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}
		return count($this->getErrors())==0;
	}
	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
	
}