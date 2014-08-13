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
 * This is a widget to view projects within a category
 *
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class Category extends FrontendBaseWidget
{
	/**
	 * The item.
	 *
	 * @var	array
	 */
	private $item;
  
	/**
	 * Execute the extra
	 */
	public function assignTemplate()
	{
		$template = FrontendTheme::getPath(FRONTEND_MODULES_PATH . '/projects/layout/widgets/category.tpl');

	//	var_dump($template);
		
		// is the content block visible?
		if(!empty($this->item))
		{
			//var_dump($this->item);
			
			// check if the given template exists
			try
			{
				//$template = FrontendTheme::getPath(FRONTEND_MODULES_PATH . '/projects/layout/widgets/' . $this->item['template']);
			}

			// template does not exist; use the default template
			catch(FrontendException $e)
			{
				// do nothing
			}
		}

		// set a default text so we don't see the template data
		//else $this->item['text'] = '';

		return $template;
	}
    
	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();
		
		$this->loadData();
		
		$template = $this->assignTemplate();
		$this->loadTemplate($template);
		
		$this->parse();
	}
	
	/**
	 * Load the data
	 */
	private function loadData()
	{
		$this->item = FrontendProjectsModel::getAllForCategory((int) $this->data['id']);
		//die(print_r($this->item));
	}
	
	/**
	 * Parse
	 */
	private function parse()
	{
		//die(print_r($this->item));
		
		if($this->item){
			$this->tpl->assign('widgetProjectsInCategory', $this->item);
		}
	}
}
