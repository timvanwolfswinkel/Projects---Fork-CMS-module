<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the detail-action
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class FrontendProjectsDetail extends FrontendBaseBlock
{

	/**
	 * The project
	 *
	 * @var	array
	 */
	private $record;

	/**
	 * The settings
	 *
	 * @var	array
	 */
	private $settings;

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
		$this->record = FrontendProjectsModel::get($this->URL->getParameter(1));

		// get settings
		$this->settings = FrontendModel::getModuleSettings('projects');

		// anything found?
		if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));
		
		// images
		$this->images = FrontendProjectsModel::getImages($this->record['id'], $this->settings);

		// overwrite URLs
		$this->record['category_full_url'] = FrontendNavigation::getURLForBlock('projects', 'category') . '/' . $this->record['category_url'];
		$this->record['full_url'] = FrontendNavigation::getURLForBlock('projects', 'detail') . '/' . $this->record['url'];

		// get tags
		$this->record['tags'] = FrontendTagsModel::getForItem('projects', $this->record['id']);
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// add to breadcrumb
		if($this->settings['allow_multiple_categories']) $this->breadcrumb->addElement($this->record['category_title'], $this->record['category_full_url']);
		$this->breadcrumb->addElement($this->record['title']);

		// set meta
		if($this->settings['allow_multiple_categories']) $this->header->setPageTitle($this->record['category_title']);
		$this->header->setPageTitle($this->record['title']);
		
		// fancybox2
		$this->header->addCSS('/frontend/modules/projects/layout/css/jquery.fancybox.css');
		$this->header->addJS('/frontend/modules/projects/js/jquery.fancybox.pack.js');
		$this->header->addCSS('/frontend/modules/projects/layout/css/jquery.fancybox-thumbs.css');
		$this->header->addJS('/frontend/modules/projects/js/jquery.fancybox-thumbs.js');
		$this->header->addJS('/frontend/modules/projects/js/project.js');

		// assign project
		$this->tpl->assign('item', $this->record);
		$this->tpl->assign('images', $this->images);

		// assign settings
		$this->tpl->assign('settings', $this->settings);
	}
	
}
