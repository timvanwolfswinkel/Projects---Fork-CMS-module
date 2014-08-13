<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;
 
/**
 * This is the add action, it will display a form to add an video to a project.
 *
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class AddVideo extends BackendBaseActionAdd
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
		$this->frm = new BackendForm('addVideo');
		$this->frm->addText('title');
		$this->frm->addTextArea('video');
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
			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			$this->frm->getField('video')->isFilled(BL::err('FieldIsRequired'));
			        
			// no errors?
			if($this->frm->isCorrect())
			{
				// build video record to insert
				$item['project_id'] = $this->project['id'];
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['embedded_url'] = $this->frm->getField('video')->getValue();
				$item['sequence'] = BackendProjectsModel::getMaximumVideosSequence($item['project_id'])+1;

				// save the item
				$item['id'] = BackendProjectsModel::saveVideo($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_video', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&project_id=' . $item['project_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
