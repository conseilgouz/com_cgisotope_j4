<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.3.3
* Package			: CG ISotope
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class com_cgisotopeInstallerScript
{
	private $min_joomla_version      = '3.10';
	private $min_php_version         = '7.2';
	private $name                    = 'CG Isotope';
	private $exttype                 = 'component';
	private $extname                 = 'cgisotope';
	private $previous_version        = '';
	private $dir           = null;
	private $lang = null;
	private $installerName = 'cgisotopeinstaller';
	public function __construct()
	{
		$this->dir = __DIR__;
		$this->lang = Factory::getLanguage();
		$this->lang->load($this->extname);
	}
    function preflight($type, $parent)
    {

		if ( ! $this->passMinimumJoomlaVersion())
		{
			$this->uninstallInstaller();

			return false;
		}

		if ( ! $this->passMinimumPHPVersion())
		{
			$this->uninstallInstaller();

			return false;
		}
		// To prevent installer from running twice if installing multiple extensions
		if ( ! file_exists($this->dir . '/' . $this->installerName . '.xml'))
		{
			return true;
		}
		$xml = simplexml_load_file(JPATH_ADMIN . '/components/com_'.$this->extname.'/'.$this->extname.'.xml');
		$this->previous_version = $xml->version;
		
    }
    
    function install($parent)
    {
    }
    
    function uninstall($parent)
    {
    }
    
    function update($parent)
    {
    }
    
    function postflight($type, $parent)
    {
        switch ($type) {
            case 'install': $message = Text::_('ISO_POSTFLIGHT_INSTALLED'); break;
            case 'uninstall': $message = Text::_('ISO_POSTFLIGHT_UNINSTALLED'); break;
            case 'update': $message = Text::_('ISO_POSTFLIGHT_UPDATED'); break;
            case 'discover_install': $message = Text::_('ISO_POSTFLIGHT_DISC_INSTALLED'); break;
        }
        $message = '<h3>'.Text::sprintf('ISO_POSTFLIGHT',$parent->getManifest()->name,$parent->getManifest()->version,$message).'</h3>';

		$changelog = $this->getChangelog();
		
		Factory::getApplication()->enqueueMessage($message.Text::_('CG_ISO_XML_DESCRIPTION').'<br>'.$changelog, 'notice');

		// Uninstall this installer
		$this->uninstallInstaller();

		return true;


//         JFactory::getApplication()->enqueueMessage($message);       
    }
	// Check if Joomla version passes minimum requirement
	private function passMinimumJoomlaVersion()
	{
		if (version_compare(JVERSION, $this->min_joomla_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Incompatible Joomla version : found <strong>' . JVERSION . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
				'error'
			);

			return false;
		}

		return true;
	}

	// Check if PHP version passes minimum requirement
	private function passMinimumPHPVersion()
	{

		if (version_compare(PHP_VERSION, $this->min_php_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
					'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
				'error'
			);
			return false;
		}

		return true;
	}
	
	private function uninstallInstaller()
	{
		if ( ! JFolder::exists(JPATH_PLUGINS . '/system/' . $this->installerName)) {
			return;
		}
		$this->delete([
			JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
			JPATH_PLUGINS . '/system/' . $this->installerName,
		]);
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		$db->execute();
		Factory::getCache()->clean('_system');
	}
	private function getChangelog()
	{
		$changelog = file_get_contents($this->dir . '/updates.txt');

		$changelog = "\n" . trim(preg_replace('#^.* \*/#s', '', $changelog));
		$changelog = preg_replace("#\r#s", '', $changelog);

		$parts = explode("\n\n", $changelog);

		if (empty($parts))
		{
			return '';
		}

		$this_version = '';

		$changelog = [];

		// Add first entry to the changelog
		$changelog[] = array_shift($parts);

		// Add extra older entries if this is an upgrade based on previous installed version
		if ($this->previous_version)
		{
			if (preg_match('#^[0-9]+-[a-z]+-[0-9]+ : v([0-9\.]+(?:-dev[0-9]+)?)\n#i', trim($changelog[0]), $match))
			{
				$this_version = $match[1];
			}

			foreach ($parts as $part)
			{
				$part = trim($part);

				if ( ! preg_match('#^[0-9]+-[a-z]+-[0-9]+ : v([0-9\.]+(?:-dev[0-9]+)?)\n#i', $part, $match))
				{
					continue;
				}

				$changelog_version = $match[1];

				if (version_compare($changelog_version, $this->previous_version, '<='))
				{
					break;
				}

				$changelog[] = $part;
			}
		}

		$changelog = implode("\n\n", $changelog);

		//  + Added   ! Removed   ^ Changed   # Fixed
		$change_types = [
			'+' => ['Added', 'success'],
			'!' => ['Removed', 'danger'],
			'^' => ['Changed', 'warning'],
			'#' => ['Fixed', 'info'],
		];
		foreach ($change_types as $char => $type)
		{
			$changelog = preg_replace(
				'#\n ' . preg_quote($char, '#') . ' #',
				"\n" . '<span class="label label-sm label-' . $type[1] . '" title="' . $type[0] . '">' . $char . '</span> ',
				$changelog
			);
		}

		// Extract note
		$note = '';
		if (preg_match('#\n > (.*?)\n#s', $changelog, $match))
		{
			$note      = $match[1];
			$changelog = str_replace($match[0], "\n", $changelog);
		}

		$changelog = preg_replace('#see: (https://www\.conseilgouz\.com[^ \)]*)#s', '<a href="\1" target="_blank">see documentation</a>', $changelog);

		$changelog = preg_replace(
			"#(\n+)([0-9]+.*?) : v([0-9\.]+(?:-dev[0-9]*)?)([^\n]*?\n+)#",
			'</pre>\1'
			. '<h4><small>\2</small> v\3</h4>'
			. '\4<pre>',
			$changelog
		);

		$changelog = str_replace(
			[
				'<pre>',
				'[FREE]',
				'[PRO]',
			],
			[
				'<pre style="line-height: 1.6em;max-height: 100px;overflow: auto;">',
				'<span class="badge badge-sm badge-success">FREE</span>',
				'<span class="badge badge-sm badge-info">PRO</span>',
			],
			$changelog
		);

		$changelog = preg_replace(
			'#\[J([1-9][\.0-9]*)\]#',
			'<span class="badge badge-sm badge-default">J\1</span>',
			$changelog
		);

		$title = Text::sprintf('ISO_LATEST',$this->name);

		if ($this->previous_version && version_compare($this->previous_version, $this_version, '<'))
		{
			$title .= ' since v' . $this->previous_version;
		}

		if ($this->previous_version
			&& $this->getMajorVersionPart($this->previous_version) < $this->getMajorVersionPart($this_version)
			&& ! $this->hasMessagesOfType('warning')
		)
		{
			JFactory::getApplication()->enqueueMessage(Text::sprintf('RLI_MAJOR_UPGRADE', Text::_($this->name)), 'warning');
		}

		if (strpos($this_version, 'dev') !== false)
		{
			$note = '';
		}

		return '<h3>' . $title . ':</h3>'
			. ($note ? '<div class="alert alert-warning">' . $note . '</div>' : '')
			. $changelog;
	}
	
}