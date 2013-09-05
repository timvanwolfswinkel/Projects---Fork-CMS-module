<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget with projects in the spotlight
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class FrontendProjectsWidgetSpotlight extends FrontendBaseWidget
{
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		// call parent
		parent::execute();

		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Parse
	 */
	private function parse()
	{
		$this->tpl->assign('widgetProjectsSpotlight', FrontendProjectsModel::getSpotlightProject());
	}
}
