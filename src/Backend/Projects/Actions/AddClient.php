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

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;
 
/**
 * This is the add action, it will display a form to create a new client.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class AddClient extends BackendBaseActionAdd
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// only one client allowed, so we redirect
		if(!BackendModel::getModuleSetting('projects', 'allow_multiple_clients', true)) $this->redirect(BackendModel::createURLForAction('clients') . '&error=only-one-client-allowed');

		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('addClient');
		$this->frm->addText('title');
        $this->frm->addImage('image');

		$this->meta = new BackendMeta($this->frm, null, 'title', true);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->meta->setURLCallback('Backend\Modules\Projects\Engine\Model', 'getURLForClient');

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
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['language'] = BL::getWorkingLanguage();
				$item['meta_id'] = $this->meta->save();
				$item['sequence'] = BackendProjectsModel::getMaximumClientSequence() + 1;

				// the image path
				$imagePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/references';
				
				// create folders if needed
				if(!\SpoonDirectory::exists($imagePath . '/300x200/')) \SpoonDirectory::create($imagePath . '/300x200/');
				if(!\SpoonDirectory::exists($imagePath . '/source/')) \SpoonDirectory::create($imagePath . '/source/');

				// is there an image provided?
				if($fields['image']->isFilled())
				{
					// build the image name
					$item['image'] = $this->meta->getUrl() . '.' . $fields['image']->getExtension();

					// upload the image & generate thumbnails
					$fields['image']->generateThumbnails($imagePath, $item['image']);
				}
				
				// save the data
				$item['id'] = BackendProjectsModel::insertClient($item);
				BackendModel::triggerEvent($this->getModule(), 'after_add_client', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('clients') . '&report=added-client&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
