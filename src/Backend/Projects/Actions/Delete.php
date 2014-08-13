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
 * This action will delete a project.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Delete extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		if($this->id !== null && BackendProjectsModel::exists($this->id))
		{
			parent::execute();
			$this->record = BackendProjectsModel::get($this->id);

			// delete item
			BackendProjectsModel::delete($this->id);
			BackendModel::triggerEvent($this->getModule(), 'after_delete', array('item' => $this->record));

			$this->redirect(BackendModel::createURLForAction('index') . '&report=deleted&var=' . urlencode($this->record['title']));
		}
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}
}
