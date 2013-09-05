<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class FrontendProjectsIndex extends FrontendBaseBlock
{
	/**
	 * @var	array
	 */
	private $items = array();

	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();

		$this->getData();
		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Load the data, don't forget to validate the incoming data
	 */
	private function getData()
	{
		$categories = FrontendProjectsModel::getCategories();
		$limit = FrontendModel::getModuleSetting('projects', 'overview_num_items_per_category', 10);
		
		foreach($categories as $item)
		{
			$item['projects'] = FrontendProjectsModel::getAllForCategory($item['id'], $limit);

			// no projects? next!
			if(empty($item['projects'])) continue;

			// add the category item including the questions
			$this->items[] = $item;
		}
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		$this->header->addCSS('/frontend/modules/projects/layout/css/projects.css');
		$this->tpl->assign('projectsCategories', (array) $this->items);
		$this->tpl->assign('allowMultipleCategories', FrontendModel::getModuleSetting('projects', 'allow_multiple_categories', true));
	}
}
