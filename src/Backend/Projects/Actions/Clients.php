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
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;

/**
 * This is the clients action, it will display the overview of clients.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Clients extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadDataGrid();

		$this->parse();
		$this->display();
	}

	/**
	 * Loads the dataGrid
	 */
	private function loadDataGrid()
	{
		// create dataGrid
		$this->dataGrid = new BackendDataGridDB(BackendProjectsModel::QRY_DATAGRID_BROWSE_CLIENTS, BL::getWorkingLanguage());
		$this->dataGrid->enableSequenceByDragAndDrop();
		$this->dataGrid->setAttributes(array('data-action' => 'sequence_clients'));			
		$this->dataGrid->setRowAttributes(array('id' => '[id]'));
		$this->dataGrid->setPaging(false);
		
		$this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit_client') . '&amp;id=[id]');
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_client') . '&amp;id=[id]', BL::lbl('Edit'));
	}

	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		parent::parse();
		$this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
	}
}
