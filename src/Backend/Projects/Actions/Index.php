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
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Core\Engine\DataGridArray as BackendDataGridArray;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;
 
/**
 * This is the index action (default), it will display the overview of projects.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Index extends BackendBaseActionIndex
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
			$dataGrid->setColumnsHidden(array('category_id', 'client_id', 'sequence'));
			
			$dataGrid->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
			$dataGrid->setColumnsSequence('dragAndDropHandle');
			$dataGrid->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
			$dataGrid->setRowAttributes(array('id' => '[id]'));
			$dataGrid->setRowAttributes(array('client' => '[client_id]'));
			
			// check if this action is allowed
			if(BackendAuthentication::isAllowedAction('edit'))
			{
				$dataGrid->addColumn('media', null, BL::lbl('Media'), BackendModel::createURLForAction('media') . '&amp;id=[id]', BL::lbl('Media'));
				$dataGrid->setColumnFunction(array(__CLASS__, 'setMediaLink'), array('[id]'), 'media');
				$dataGrid->setColumnFunction(array(__CLASS__, 'setClientLink'), array('[client_id]', '[client]'), 'client');
				$dataGrid->setColumnAttributes('media', array('style' => 'width: 1%;'));
				$dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
				$dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit') . '&amp;id=[id]', BL::lbl('Edit'));
			}
			
			// add dataGrid to list
			$this->dataGrids[] = array('id' => $categoryId, 'title' => $categoryTitle, 'content' => $dataGrid->getContent());
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
	 * Sets a link to the media overview
	 *
	 * @param int $projectId The specific id of the project
	 * @return string
	 */
	public static function setMediaLink($projectId)
	{
		return '<a class="button icon iconEdit linkButton" href="' . BackendModel::createURLForAction('media') . '&project_id=' . $projectId . '">
					<span>' . BL::lbl('ManageMedia') . '</span>
				</a>';
	}
	
	/**
	 * Sets a link for client when available
	 *
	 * @param int $clientId The specific id of the client
	 * @param string $client The specific name of the client
	 * @return string
	 */
	public static function setClientLink($clientId, $client)
	{
		if ( $clientId > 1 ){
			return '<a href="' . BackendModel::createURLForAction('edit_client') . '&amp;id=' . $clientId . '"><span>' . $client . '</span></a>';
		} else return '<span>' . BL::lbl('NoProjectReference') . '</span>';	
	}
}
