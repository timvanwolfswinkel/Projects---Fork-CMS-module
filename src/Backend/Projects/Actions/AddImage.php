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
use Backend\Modules\Projects\Engine\Helper as BackendProjectsHelper;
 
/**
 * This is the add action, it will display a form to add an image to a project.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class AddImage extends BackendBaseActionAdd
{
	/**
	 * The project record
	 *
	 * @var	array
	 */
	private $project;

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
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		
		// the project does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the necessary data
	 */
	private function getData()
	{
		$this->project = BackendProjectsModel::get($this->getParameter('project_id', 'int'));
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('addImage');
		$this->frm->addText('title');
		$this->frm->addImage('image');
	}

	/**
	 * Parses stuff into the template
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('project', $this->project);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$image = $this->frm->getField('image');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			$image->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['project_id'] = $this->project['id'];
				$item['title'] = $this->frm->getField('title')->getValue();

				// set files path for this record
				$path = FRONTEND_FILES_PATH . '/' . $this->module . '/' . $item['project_id'];

				// set formats
				$formats = array();
				$formats[] = array('size' => '64x64', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
				$formats[] = array('size' => '128x128', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width1') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height1'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment1'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio1'));
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width2') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height2'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment2'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio2'));
				$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width3') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height3'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment3'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio3'));
				//$formats[] = array('size' => BackendModel::getModuleSetting($this->URL->getModule(), 'width4') . 'x' . BackendModel::getModuleSetting($this->URL->getModule(), 'height4'), 'allow_enlargement' => BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment4'), 'force_aspect_ratio' => BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio4'));

				// set the filename
				$item['filename'] = time() . '.' . $image->getExtension();
				$item['sequence'] = BackendProjectsModel::getMaximumImagesSequence($item['project_id'])+1;

				// add images
				BackendProjectsHelper::addImages($image, $path, $item['filename'], $formats);

				// save the item
				$item['id'] = BackendProjectsModel::saveImage($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_image', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&project_id=' . $item['project_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
