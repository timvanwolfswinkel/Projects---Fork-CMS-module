<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;

/**
 * This is the media action, it will display the overview of media for a specific project.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Media extends BackendBaseActionIndex
{
	/**
	 * The project record
	 *
	 * @var	array
	 */
	private $project = array();
    
	/**
	 * Datagrid with published items
	 *
	 * @var	SpoonDataGrid
	 */
	private $dgImages, $dgFiles, $dgVideos;

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
			$this->loadDataGridImages();
			$this->loadDataGridFiles();
			$this->loadDataGridVideos();
			$this->parse();
			$this->display();
		}
		
		// the project does not exist
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
	 * Loads the datagrid of the images
	 */
	protected function loadDataGridImages()
	{
		// set image link
		$imageLink = FRONTEND_FILES_URL . '/' . $this->module . '/[project_id]/64x64';
	  
		// create images datagrid
		$this->dgImages = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE_IMAGES, $this->id);
		$this->dgImages->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
		$this->dgImages->setAttributes(array('id' => 'projects_images_dg'));
		$this->dgImages->setAttributes(array('data-action' => 'sequence_images'));			
		  
		$this->dgImages->setColumnHidden('sequence');
		$this->dgImages->setColumnHidden('project_id');
		  
		$this->dgImages->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
		$this->dgImages->setColumnsSequence('dragAndDropHandle');
		$this->dgImages->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
			  
		$this->dgImages->setRowAttributes(array('data-id' => '[id]'));	
		$this->dgImages->setSortingColumns(array('title', 'sequence'), 'sequence');
		$this->dgImages->setSortParameter('asc');
		$this->dgImages->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_image') . '&amp;id=[id]&amp;project_id=[project_id]', BL::lbl('Edit'));
	      
		$this->dgImages->setColumnFunction(array(new BackendDataGridFunctions(), 'showImage'), array($imageLink, '[filename]'), 'filename' );
		$this->dgImages->setColumnAttributes('filename', array('class' => 'thumbnail'));
		$this->dgImages->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
		$this->dgImages->setColumnsSequence('checkbox');
	      
		$ddmMassAction = new \SpoonFormDropdown('action', array('deleteImages' => BL::lbl('Delete')), 'deleteImages');
		$this->dgImages->setMassAction($ddmMassAction);
		$this->dgImages->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}

	/**
	 * Loads the datagrid of the files
	 */
	protected function loadDataGridFiles()
	{
		// create files datagrid
		$this->dgFiles = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE_FILES, $this->id);
		
		$this->dgFiles->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
		$this->dgFiles->setAttributes(array('id' => 'projects_files_dg'));
		$this->dgFiles->setAttributes(array('data-action' => 'sequence_files'));
	      
		$this->dgFiles->setColumnHidden('sequence');
		$this->dgFiles->setColumnHidden('project_id');
	      
		$this->dgFiles->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
		$this->dgFiles->setColumnsSequence('dragAndDropHandle');
		$this->dgFiles->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
	      
		$this->dgFiles->setRowAttributes(array('data-id' => '[id]'));	
	      
		$this->dgFiles->setSortingColumns(array('title', 'sequence'), 'sequence');
		$this->dgFiles->setSortParameter('asc');
	
		$this->dgFiles->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_file') . '&amp;id=[id]&amp;project_id=[project_id]', BL::lbl('Edit'));      
		$this->dgFiles->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
		$this->dgFiles->setColumnsSequence('checkbox');
	      
		$ddmMassAction = new \SpoonFormDropdown('action', array('deleteFiles' => BL::lbl('Delete')), 'deleteFiles');
		$this->dgFiles->setMassAction($ddmMassAction);
		$this->dgFiles->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}

	/**
	 * Loads the datagrid of the videos
	 */
	protected function loadDataGridVideos()
	{
		// create videos datagrid
		$this->dgVideos = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE_VIDEOS, $this->id);
		  
		$this->dgVideos->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
		$this->dgVideos->setAttributes(array('id' => 'projects_videos_dg'));
		$this->dgVideos->setAttributes(array('data-action' => 'sequence_videos'));
	      
		$this->dgVideos->setColumnHidden('project_id');
	      
		$this->dgVideos->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
		$this->dgVideos->setColumnsSequence('dragAndDropHandle');
		$this->dgVideos->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
		
		$this->dgVideos->setRowAttributes(array('data-id' => '[id]'));	
	      
		$this->dgVideos->setSortingColumns(array('title', 'sequence'), 'sequence');
		$this->dgVideos->setSortParameter('asc');
	  
		$this->dgVideos->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_video') . '&amp;id=[id]&amp;project_id=[project_id]', BL::lbl('Edit'));
		$this->dgVideos->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
		$this->dgVideos->setColumnsSequence('checkbox');
		
		$ddmMassAction = new \SpoonFormDropdown('action', array('deleteVideos' => BL::lbl('Delete')), 'deleteVideos');
		$this->dgVideos->setMassAction($ddmMassAction);
		$this->dgVideos->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}
	
	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		$this->tpl->assign('dataGridImages', ($this->dgImages->getNumResults() != 0) ? $this->dgImages->getContent() : false);
		$this->tpl->assign('dataGridFiles', ($this->dgFiles->getNumResults() != 0) ? $this->dgFiles->getContent() : false);
		$this->tpl->assign('dataGridVideos', ($this->dgVideos->getNumResults() != 0) ? $this->dgVideos->getContent() : false);
        
		$this->tpl->assign('project', $this->project);
	}
}
