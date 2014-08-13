<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;

/**
 * This action will delete a client.
 *
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class DeleteClient extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendProjectsModel::existsClient($this->id))
		{
			$this->record = (array) BackendProjectsModel::getClient($this->id);

			if(BackendProjectsModel::deleteClientAllowed($this->id))
			{
				parent::execute();

				// delete item
				BackendProjectsModel::deleteClient($this->id);
				BackendModel::triggerEvent($this->getModule(), 'after_delete_client', array('item' => $this->record));

				// category was deleted, so redirect
				$this->redirect(BackendModel::createURLForAction('clients') . '&report=deleted-client&var=' . urlencode($this->record['title']));
			}
			else $this->redirect(BackendModel::createURLForAction('clients') . '&error=delete-client-not-allowed&var=' . urlencode($this->record['title']));
		}
		else $this->redirect(BackendModel::createURLForAction('clients') . '&error=non-existing');
	}
}
