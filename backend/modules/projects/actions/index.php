<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class BackendProjectsIndex extends BackendBaseActionIndex
{
	/**
	 * The dataGrids
	 *
	 * @var	array
	 */
	private $dataGrids, $emptyDatagrid;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadDatagrids();

		$this->parse();
		$this->display();
	}

	/**
	 * Loads the dataGrids
	 */
	private function loadDatagrids()
	{
		// load all categories
		$categories = BackendProjectsModel::getCategories(true);
		
		// loop categories and create a dataGrid for each one
		foreach($categories as $categoryId => $categoryTitle)
		{
			$dataGrid = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE, array(BL::getWorkingLanguage(), $categoryId));
			$dataGrid->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
			$dataGrid->setAttributes(array('id' => 'projects_dg'));
			$dataGrid->setColumnsHidden(array('category_id', 'sequence'));
			
			$dataGrid->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
			$dataGrid->setColumnsSequence('dragAndDropHandle');
			$dataGrid->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
			$dataGrid->setRowAttributes(array('id' => '[id]'));

			// check if this action is allowed
			if(BackendAuthentication::isAllowedAction('edit'))
			{
				$dataGrid->addColumn('images', null, BL::lbl('Images'));
				$dataGrid->setColumnFunction(array(__CLASS__, 'setImagesLink'), array('[id]'), 'images');
				$dataGrid->setColumnAttributes('images', array('style' => 'width: 1%;'));
				$dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
				$dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]', BL::lbl('Edit'));
			}

			// add dataGrid to list
			$this->dataGrids[] = array('id' => $categoryId,
									   'title' => $categoryTitle,
									   'content' => $dataGrid->getContent());
		}

		// set empty datagrid
		$this->emptyDatagrid = new BackendDataGridArray(array(array('dragAndDropHandle' => '', 'title' => BL::msg('NoProjectsInCategory'), 'edit' => '')));
		$this->emptyDatagrid->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop emptyGrid'));
		$this->emptyDatagrid->setHeaderLabels(array('edit' => null, 'dragAndDropHandle' => null));
	}

	/**
	 * Parse the dataGrids and the reports
	 */
	protected function parse()
	{
		parent::parse();

		// parse dataGrids
		if(!empty($this->dataGrids)) $this->tpl->assign('dataGrids', $this->dataGrids);
		$this->tpl->assign('emptyDatagrid', $this->emptyDatagrid->getContent());
	}

	/**
	 * Datagrid method, sets a link to the images overview for the slideshow if
	 * a module was not specified
	 *
	 * @param string $module The module string (which shouldn't be an empty one).
	 * @param int $slideshowID The slideshow ID used in the URL parameters.
	 * @return string
	 */
	public static function setImagesLink($projectId)
	{
		return '<a class="button icon iconEdit linkButton" href="' . BackendModel::createURLForAction('images') . '&project_id=' . $projectId . '">
					<span>' . BL::lbl('ManageImages') . '</span>
				</a>';
	}
}
