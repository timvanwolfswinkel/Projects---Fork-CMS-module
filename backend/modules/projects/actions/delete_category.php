<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete a category
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class BackendProjectsDeleteCategory extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendProjectsModel::existsCategory($this->id))
		{
			$this->record = (array) BackendProjectsModel::getCategory($this->id);

			if(BackendProjectsModel::deleteCategoryAllowed($this->id))
			{
				parent::execute();

				// delete item
				BackendProjectsModel::deleteCategory($this->id);
				BackendModel::triggerEvent($this->getModule(), 'after_delete_category', array('item' => $this->record));

				// category was deleted, so redirect
				$this->redirect(BackendModel::createURLForAction('categories') . '&report=deleted-category&var=' . urlencode($this->record['title']));
			}
			else $this->redirect(BackendModel::createURLForAction('categories') . '&error=delete-category-not-allowed&var=' . urlencode($this->record['title']));
		}
		else $this->redirect(BackendModel::createURLForAction('categories') . '&error=non-existing');
	}
}
