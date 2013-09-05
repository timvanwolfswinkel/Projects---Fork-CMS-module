<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the images-action, it will display the overview of images for a specific project
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class BackendProjectsImages extends BackendBaseActionIndex
{
	/**
	 * The project record
	 *
	 * @var	array
	 */
	private $project = array();

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('project_id', 'int');
		if($this->id !== null && BackendProjectsModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadDataGrid();
			$this->parse();
			$this->display();
		}
		// the image does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Gets all necessary data
	 */
	protected function getData()
	{
		$this->project = BackendProjectsModel::get($this->id);
	}

	/**
	 * Loads the datagrids
	 */
	protected function loadDataGrid()
	{
		$imageLink = FRONTEND_FILES_URL . '/' . $this->module . '/[project_id]/64x64';

		// create datagrid
		$this->dataGrid = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE_IMAGES, $this->id);
		$this->dataGrid->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
		$this->dataGrid->setAttributes(array('id' => 'projects_images_dg'));	
		$this->dataGrid->setColumnHidden('sequence');
		$this->dataGrid->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
		$this->dataGrid->setColumnsSequence('dragAndDropHandle');
		$this->dataGrid->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
		$this->dataGrid->setRowAttributes(array('data-id' => '[id]'));	
		$this->dataGrid->setColumnHidden('project_id');
		$this->dataGrid->setSortingColumns(array('title', 'sequence'), 'sequence');
		$this->dataGrid->setSortParameter('asc');
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_image') . '&amp;id=[id]&amp;project_id=[project_id]', BL::lbl('Edit'));
		
			//$this->dataGrid->enableSequenceByDragAndDrop();
			
		$this->dataGrid->setColumnFunction(array('BackendDataGridFunctions', 'showImage'), array($imageLink, '[filename]'), 'filename' );
		$this->dataGrid->setColumnAttributes('filename', array('class' => 'thumbnail'));
		$this->dataGrid->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
		$this->dataGrid->setColumnsSequence('checkbox');
		
		$ddmMassAction = new SpoonFormDropdown('action', array('delete' => BL::lbl('Delete')), 'delete');
		$this->dataGrid->setMassAction($ddmMassAction);
		$this->dataGrid->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}

	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		$this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
		$this->tpl->assign('project', $this->project);
	}
}
