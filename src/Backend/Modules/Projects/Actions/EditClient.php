<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
 
use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;

/**
 * This is the edit client action, it will display a form to edit an existing client.
 *
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl
 */
class EditClient extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist?
		if($this->id !== null && BackendProjectsModel::existsClient($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();

			$this->parse();
			$this->display();
		}
		else $this->redirect(BackendModel::createURLForAction('clients') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		$this->record = BackendProjectsModel::getClient($this->id);
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('editClient');
		$this->frm->addText('title', $this->record['title']);
		$this->frm->addImage('image');

		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// assign the data
		$this->tpl->assign('item', $this->record);
		$this->tpl->assign('showProjectsDeleteClient', BackendProjectsModel::deleteClientAllowed($this->id) && BackendAuthentication::isAllowedAction('delete_client'));
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->meta->setUrlCallback('Backend\Modules\Projects\Engine\Model', 'getURLForClient', array($this->record['id']));

			$this->frm->cleanupFields();

			// validate fields
			$fields = $this->frm->getFields();
			$fields['title']->isFilled(BL::err('TitleIsRequired'));
			
			if($fields['image']->isFilled())
			{
				$fields['image']->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
				$fields['image']->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
			}
			
			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['language'] = $this->record['language'];
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['meta_id'] = $this->meta->save(true);

				// the image path
				$imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/references';
				
				// create folders if needed
				if(!\SpoonDirectory::exists($imagePath . '/150x150/')) \SpoonDirectory::create($imagePath . '/150x150/');
				if(!\SpoonDirectory::exists($imagePath . '/source/')) \SpoonDirectory::create($imagePath . '/source/');

				// image provided?
				if($fields['image']->isFilled())
				{
					// build the image name
					$item['image'] = $this->meta->getUrl() . '.' . $fields['image']->getExtension();

					// upload the image & generate thumbnails
					$fields['image']->generateThumbnails($imagePath, $item['image']);
				}
				
				// update the item
				BackendProjectsModel::updateClient($item);
				BackendModel::triggerEvent($this->getModule(), 'after_edit_client', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('clients') . '&report=edited-client&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
