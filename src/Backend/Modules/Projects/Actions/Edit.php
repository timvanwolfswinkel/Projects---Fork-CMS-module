<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
 
/**
 * This is the edit action, it will display a form to edit an existing project.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class Edit extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exists
		if($this->id !== null && BackendProjectsModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();

			$this->parse();
			$this->display();
		}
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		$this->record = (array) BackendProjectsModel::get($this->id);
		$this->categories = (array) BackendProjectsModel::getCategories();
		$this->clients = (array) BackendProjectsModel::getClients();
		$this->allProjectsGroupedByCategories = (array) BackendProjectsModel::getAllProjectsGroupedByCategories();
		$this->relatedProjects = (array) BackendProjectsModel::getRelatedProjects($this->id);
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		// get values for the form
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');
		
		// create form
		$this->frm = new BackendForm('edit');
		$this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('introduction', $this->record['introduction']);
		$this->frm->addEditor('text', $this->record['text']);
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, $this->record['hidden']);
		$this->frm->addCheckbox('spotlight', ($this->record['spotlight']=='N' ? false : true) );
		$this->frm->addDropdown('related_projects', $this->allProjectsGroupedByCategories, $this->relatedProjects, true );
		$this->frm->addDate('date_date', $this->record['date']);
		$this->frm->addDropdown('category_id', $this->categories, $this->record['category_id']);
		$this->frm->addDropdown('client_id', $this->clients, $this->record['client_id']);
		$this->frm->addText('tags', BackendTagsModel::getTags($this->URL->getModule(), $this->record['id']), null, 'inputText tagBox', 'inputTextError tagBox');

		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);

		// assign the active record and additional variables
		$this->tpl->assign('item', $this->record);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->meta->setUrlCallback('Backend\Modules\Projects\Engine\Model', 'getURL', array($this->record['id']));

			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
			$this->frm->getField('introduction')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('text')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('category_id')->isFilled(BL::err('CategoryIsRequired'));
			
			$this->frm->getField('date_date')->isValid(BL::err('DateIsInvalid'));
			
			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['meta_id'] = $this->meta->save(true);
				$item['category_id'] = $this->frm->getField('category_id')->getValue();
				$item['client_id'] = $this->frm->getField('client_id')->getValue();
				$item['language'] = $this->record['language'];
				$item['date'] = BackendModel::getUTCDate(null, BackendModel::getUTCTimestamp($this->frm->getField('date_date')));
				$item['edited_on'] = BackendModel::getUTCDate();
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['introduction'] = $this->frm->getField('introduction')->getValue(true);
				$item['text'] = $this->frm->getField('text')->getValue(true);
				$item['hidden'] = $this->frm->getField('hidden')->getValue();
				$item['spotlight'] = ($this->frm->getField('spotlight')->getValue()==false ? 'N' : 'Y');
				
				// update the item
				BackendProjectsModel::update($item);
				BackendTagsModel::saveTags($item['id'], $this->frm->getField('tags')->getValue(), $this->URL->getModule());
				BackendProjectsModel::saveRelatedProjects($item['id'], $this->frm->getField('related_projects')->getValue(), $this->relatedProjects);
				
				BackendModel::triggerEvent($this->getModule(), 'after_edit', array('item' => $item));

				// edit search index
				BackendSearchModel::saveIndex('projects', $item['id'], array('title' => $item['title'], 'text' => $item['text']));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=saved&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
