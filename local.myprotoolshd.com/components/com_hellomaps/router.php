<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
 
 function HellomapsBuildRoute( &$query )
{
	$segments = array();

	if(isset($query['controller']))
	{
		$segments[0] = $query['controller'];
		unset( $query['controller'] );
		if(isset($query['task']))
		{
			$segments[1] = $query['task'];
			unset( $query['task'] );

			if(isset($query['view']))
			{
				$segments[2] = $query['view'];
				unset( $query['view'] );

				if(isset($query['format']))
				{
					$segments[3] = $query['format'];
					unset( $query['format'] );
				}
			}
		}
	}

	return $segments;

}


function HellomapsParseRoute( $segments )
{

	$vars = array();
	switch (count($segments))
	{
		case 0:
			break;
		case 1:
			$vars['controller'] = $segments[0];
			break;
		case 2:
			$vars['controller'] = $segments[0];
			$vars['task'] = $segments[1];
			break;
		case 3:
			$vars['controller'] = $segments[0];
			$vars['task'] = $segments[1];
			$vars['view'] = $segments[2];
			break;
		case 4:
			$vars['controller'] = $segments[0];
			$vars['task'] = $segments[1];
			$vars['view'] = $segments[2];
			$vars['format']=$segments[3];
			break;
		
		default: return $vars;
	}
	return $vars;
}