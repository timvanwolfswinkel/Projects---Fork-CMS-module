<?php

namespace Frontend\Modules\Projects\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Projects\Engine\Model as FrontendProjectsModel;

/**
 * This is a widget to view a project specific header
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Header extends FrontendBaseWidget
{
    private $project;
  
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		// call parent
		parent::execute();

		// code
        
		$this->loadTemplate();
		$this->parse();
	}
    
	/**
	 * Parse
	 */
	private function parse()
	{
		//$this->tpl->assign('widgetProjectsCategory', $this->project);
	}
}
