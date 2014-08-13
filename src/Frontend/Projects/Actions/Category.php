<?php

namespace Frontend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Projects\Engine\Model as FrontendProjectsModel;

/**
 * This is the detail-action
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Category extends FrontendBaseBlock
{

	/**
	 * @var	array
	 */
	private $projects;

	/**
	 * @var	array
	 */
	private $record;

	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();

		// hide contentTitle, in the template the title is wrapped with an inverse-option
		$this->tpl->assign('hideContentTitle', true);

		$this->loadTemplate();
		$this->getData();
		$this->parse();
	}

	/**
	 * Load the data, don't forget to validate the incoming data
	 */
	private function getData()
	{
		// validate incoming parameters
		if($this->URL->getParameter(1) === null) $this->redirect(FrontendNavigation::getURL(404));

		// get by URL
		$this->record = FrontendProjectsModel::getCategory($this->URL->getParameter(1));
		
		// anything found?
		if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));
		
		// overwrite URLs
		$this->record['full_url'] = FrontendNavigation::getURLForBlock('Projects', 'Category') . '/' . $this->record['url'];
		$this->projects = FrontendProjectsModel::getAllForCategory($this->record['id']);
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		$this->header->addCSS('/src/Frontend/Modules/Projects/Layout/Css/projects.css');
		
		// add to breadcrumb
		$this->breadcrumb->addElement($this->record['title']);

		// set meta
		$this->header->setPageTitle($this->record['title']);

		// assign record and project
		$this->tpl->assign('category', $this->record);
		$this->tpl->assign('projects', $this->projects);
	}
	
}
