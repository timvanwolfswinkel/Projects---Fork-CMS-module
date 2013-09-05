<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action is used to perform mass actions on project images (delete, ...)
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class BackendProjectsMassAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		
		// action to execute
		$action = SpoonFilter::getGetValue('action', array('delete'), 'delete');
		
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') . '&error=no-selection');
		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];
			$slideshowID = (int) $_GET['project_id'];

			// delete comment(s)
			if($action == 'delete') BackendProjectsModel::deleteImage($aIds);
		}

		$this->redirect(BackendModel::createURLForAction('images') . '&project_id=' . $slideshowID . '&report=deleted');
	}
}
