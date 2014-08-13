<?php

namespace Backend\Modules\Projects\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;
 
/**
 * This is the add action, it will display a form to create a new project.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Add extends BackendBaseActionAdd
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
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
		// create form
		$this->frm = new BackendForm('add');

		// set hidden values
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden', $this->URL->getModule()), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');
		$categories = BackendProjectsModel::getCategories();
		$clients = BackendProjectsModel::getClients();
		$allProjectsGroupedByCategories = BackendProjectsModel::getAllProjectsGroupedByCategories();
		
		// create elements
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('introduction');
		$this->frm->addEditor('text');
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, 'N');
		$this->frm->addDate('date_date');
		$this->frm->addCheckbox('spotlight');
		$this->frm->addDropdown('related_projects', $allProjectsGroupedByCategories, null, true);
		$this->frm->addDropdown('category_id', $categories);
		$this->frm->addDropdown('client_id', $clients);
		$this->frm->addText('tags', null, null, 'inputText tagBox', 'inputTextError tagBox');
		
		// meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);

		// parse additional variables
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
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
				$item['meta_id'] = $this->meta->save();
				$item['category_id'] = $this->frm->getField('category_id')->getValue();
				$item['client_id'] = $this->frm->getField('client_id')->getValue();
				$item['user_id'] = BackendAuthentication::getUser()->getUserId();
				$item['language'] = BL::getWorkingLanguage();
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['introduction'] = $this->frm->getField('introduction')->getValue(true);
				$item['text'] = $this->frm->getField('text')->getValue(true);
				$item['date'] = BackendModel::getUTCDate(null, BackendModel::getUTCTimestamp($this->frm->getField('date_date')));
				$item['edited_on'] = BackendModel::getUTCDate();
				$item['created_on'] = BackendModel::getUTCDate();
				$item['hidden'] = $this->frm->getField('hidden')->getValue();
				$item['spotlight'] = ($this->frm->getField('spotlight')->getValue()==false ? 'N' : 'Y');
				$item['sequence'] = BackendProjectsModel::getMaximumSequence($this->frm->getField('category_id')->getValue()) + 1;
				
				// save the data
				$item['id'] = BackendProjectsModel::insert($item);
				BackendTagsModel::saveTags($item['id'], $this->frm->getField('tags')->getValue(), $this->URL->getModule());
				BackendProjectsModel::saveRelatedProjects($item['id'], $this->frm->getField('related_projects')->getValue());
				
				BackendModel::triggerEvent($this->getModule(), 'after_add', array('item' => $item));

				// add search index
				BackendSearchModel::saveIndex('projects', $item['id'], array('title' => $item['title'], 'text' => $item['text']));
				$this->redirect(BackendModel::createURLForAction('index') . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
