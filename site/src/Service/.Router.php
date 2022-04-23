<?php
/**
* CG Isotope Component  - Joomla 4.0.0 Component 
* Version			: 2.0.10
* Package			: CG ISotope
* copyright 		: Copyright (C) 2021 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* From              : isotope.metafizzy.co
*/
namespace ConseilGouz\Component\CGIsotope\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;

/**
 * Routing class from com_banners
 *
 * @since  3.3
 */
class Router extends RouterBase
{
	/**
	 * Build the route for the com_banners component
	 *
	 * @param   array  $query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		$total = \count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = \count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// View is always the first element of the array
		$count = \count($segments);

		if ($count)
		{
			$count--;
			$segment = array_shift($segments);

			if (\is_numeric($segment))
			{
				$vars['id'] = $segment;
			}
			else
			{
				$vars['task'] = $segment;
			}
		}

		if ($count)
		{
			$segment = array_shift($segments);

			if (\is_numeric($segment))
			{
				$vars['id'] = $segment;
			}
		}

		return $vars;
	}
}
