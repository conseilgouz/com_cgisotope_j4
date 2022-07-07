<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.2
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use ConseilGouz\Component\CGIsotope\Site\Helper\CGHelper;

class PagesController extends AdminController
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_CGISOTOPE_PAGES';
	private static $characters = array('=', '+', '-', '@');
	    
	public function getModel($name = 'Page', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	/**
	 * Method to copy
	 *
	 * @return  void
	 */
	public function copy()
	{
	    // Check for request forgeries.
	    $this->checkToken();
	    // Get the isotope data
        $app = Factory::getApplication();
        $input = $app->input;
		$pks = $input->post->get('cid', array(), 'array');
        $db    = Factory::getDbo();
		foreach ($pks as $id)	{
            $result = $db->setQuery(
                $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__cgisotope_page'))
                ->where($db->quoteName('id') . ' = ' . (int)$id)
            )->loadObject();
            /*if (count($result) != 1) {
                $this->setMessage(Text::sprintf('CG_ISO_MODULE_SELECT_ERROR', $id), 'warning');
                $this->setRedirect(JRoute::_('index.php?option=com_cgisotope', false));
                return false;
            }*/
            $data = new \stdClass;
            $data->id= 0;
            $data->title = $this->check_title($result->title);
            $data->state = $result->state;
            $data->language = $result->language;            
            $data->page_params =  $result->page_params;
            $data->sections = $result->sections;
            $data->fieldslinks = $result->fieldslinks;
            $ret = $db->insertObject('#__cgisotope_page', $data,'id');
            if (!$ret) {
                JError::raiseWarning(100, Text::_('COM_CGISOTOPE_ERROR_CREATE'));
                return false;
            }
		}
		$this->setRedirect(Route::_('index.php?option=com_cgisotope&view=pages', false));		
		return true;
	}
	
	/**
	 * Method to export
	 *
	 * @return  void
	 */
	public function export()
	{
	    // Check for request forgeries.
	    $this->checkToken();
		// require_once JPATH_COMPONENT . '/helpers/helper.php';		
	    $task = $this->getTask();
	    $pks = array();
	    if ($task == 'export') {
	        // Get selected files
	        $pks = $this->input->post->get('cid');
	    }
	    $model = $this->getModel('Pages');
	    // Get the isotope data
	    $data = $model->getDataAsIterator($pks);
	    if (count($data)) {
	        try
	        {
	            $rows = self::getJSONData($data);
	        }
	        catch (InvalidArgumentException $exception)
	        {
	            $this->setMessage(Text::_('Export error'), 'error');
	            $this->setRedirect(Route::_('index.php?option=com_cgisotope&view=pages', false));
	            
	            return;
	        }
	        
	        // Destroy the iterator now
	        unset($data);
	        
	        $date     = new Date('now', new \DateTimeZone('UTC'));
	        $filename = 'cgisotope_' . $date->format('Y-m-d_His_T');
	        
	        $app = Factory::getApplication();
	        $app->setHeader('Content-Type', 'application/json', true)
	        ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.json"', true)
	        ->setHeader('Cache-Control', 'must-revalidate', true)
	        ->sendHeaders();
	        
	        $output = fopen("php://output", "w");
	        
	        foreach ($rows as $row)
	        {
	            fwrite($output, json_encode($row));
	        }
	        
	        fclose($output);
	        $app->triggerEvent('onAfterCGIsotopeExport', array());
	        $app->close();
	    }
	    else
	    {
	        $this->setMessage(Text::_('NO ISOTOPE PAGE TO EXPORT'));
	        $this->setRedirect(Route::_('index.php?option=com_cgisotope&view=pages', false));
	    }
	}

	public static function getJSONData($data)
	{
	    if (!is_iterable($data))
	    {
	        throw new InvalidArgumentException(
	            sprintf(
	                '%s() requires an array or object implementing the Traversable interface, a %s was given.',
	                __METHOD__,
	                gettype($data) === 'object' ? get_class($data) : gettype($data)
	                )
	            );
	    }
	    
	    foreach ($data as $iso)
	    {
	        yield array(
	            'cgisotope'  => CGHelper::getCGVersion(),
	            'id'         => $iso->id,
	            'title'      => self::escapeCsvFormula($iso->title),
				'state'  	 => $iso->state,
	            'page_params'    => self::escapeCsvFormula($iso->page_params),
	            'sections'   => self::escapeCsvFormula($iso->sections),
	            'fieldslinks'=> self::escapeCsvFormula($iso->fieldslinks),
	            'language'   => self::escapeCsvFormula($iso->language)
	        );
	    }
	}
	
	/**
	 * Escapes potential characters that start a formula in a CSV value to prevent injection attacks
	 *
	 * @param   mixed  $value  csv field value
	 *
	 * @return  mixed
	 *
	 * @since   3.9.7
	 */
	protected static function escapeCsvFormula($value)
	{
	    if ($value == '')
	    {
	        return $value;
	    }
	    
	    if (in_array($value[0], self::$characters, true))
	    {
	        $value = ' ' . $value;
	    }
	    
	    return $value;
	}
	public function import()
	{
	    // Check for request forgeries.
	    $this->checkToken();
	    $task = $this->getTask();
		$files        = $this->input->files->get('Filedata', array(), 'array');
		$return       = Factory::getSession()->get('com_cgisotope.return_url');
		$this->folder = $this->input->get('folder', '', 'path');
		$this->setRedirect($return);
        $db    = Factory::getDbo();
		foreach ($files as &$file) {
		    if ($file[type] != "application/json") {
		        JError::raiseWarning(100, Text::_('COM_CGISOTOPE_ERROR_FILE_TYPE'));
		        return false;
		    }
            $strJsonFileContents = file_get_contents($file[tmp_name]);
            $result = json_decode($strJsonFileContents, true);
            if (!$result['cgisotope']) {
                JError::raiseWarning(100, Text::_('COM_CGISOTOPE_ERROR_NOT_ISOTOPE'));
                return false;
            }
            $version = $result['cgisotope']; // version CG Isotope
            $data = new \stdClass;
            $data->id= 0;
            $data->title = $this->check_title($result['title']);
            $data->state = $result['state'];
            $data->language = $result['language'];            
            $data->page_params =  $result['page_params'];
            $data->sections = $result['sections'];
            $data->fieldslinks = $result['fieldslinks'];
            $ret = $db->insertObject('#__cgisotope_page', $data,'id');
            if (!$ret) {
                JError::raiseWarning(100, Text::_('COM_CGISOTOPE_ERROR_CREATE'));
                return false;
            }

		}
		return true;
	}
	function check_title($title) {
        $db    = Factory::getDbo();
        do {
			$result = $db->setQuery(
                $db->getQuery(true)
                ->select('count("*")')
                ->from($db->quoteName('#__cgisotope_page'))
                ->where($db->quoteName('title') . ' like ' . $db->quote($title) .' AND state in (0,1)')
            )->loadResult();
			if ($result > 0) $title = StringHelper::increment($title);
		}
		while ($result > 0);
		return $title;
	}
	
}