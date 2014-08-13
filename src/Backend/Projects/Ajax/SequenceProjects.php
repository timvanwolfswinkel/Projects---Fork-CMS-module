<?php

namespace Backend\Modules\Projects\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Modules\Projects\Engine\Model as BackendProjectsModel;

/**
 * Reorder projects
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class SequenceProjects extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
				
		$projectId = \SpoonFilter::getPostValue('projectId', null, '', 'int');
		$fromCategoryId = \SpoonFilter::getPostValue('fromCategoryId', null, '', 'int');
		$toCategoryId = \SpoonFilter::getPostValue('toCategoryId', null, '', 'int');
		$fromCategorySequence = \SpoonFilter::getPostValue('fromCategorySequence', null, '', 'string');
		$toCategorySequence = \SpoonFilter::getPostValue('toCategorySequence', null, '', 'string');

		// invalid project id
		if(!BackendProjectsModel::exists($projectId)) $this->output(self::BAD_REQUEST, null, 'project does not exist');

		// list ids
		$fromCategorySequence = (array) explode(',', ltrim($fromCategorySequence, ','));
		$toCategorySequence = (array) explode(',', ltrim($toCategorySequence, ','));

		// is the project moved to a new category?
		if($fromCategoryId != $toCategoryId)
		{
			$item['id'] = $projectId;
			$item['category_id'] = $toCategoryId;

			BackendProjectsModel::update($item);

			// loop id's and set new sequence
			foreach($toCategorySequence as $i => $id)
			{
				$item = array();
				$item['id'] = (int) $id;
				$item['sequence'] = $i + 1;

				// update sequence if the item exists
				if(BackendProjectsModel::exists($item['id'])) BackendProjectsModel::update($item);
			}
		}

		// loop id's and set new sequence
		foreach($fromCategorySequence as $i => $id)
		{
			$item['id'] = (int) $id;
			$item['sequence'] = $i + 1;

			// update sequence if the item exists
			if(BackendProjectsModel::exists($item['id'])) BackendProjectsModel::update($item);
		}

		// success output
		$this->output(self::OK, null, 'sequence updated');
	}
}
