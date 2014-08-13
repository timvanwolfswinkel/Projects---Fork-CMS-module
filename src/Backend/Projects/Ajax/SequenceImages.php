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
 * Reorder images
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class SequenceImages extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		//die('test');
		
		// get parameters
		$newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

		// list id
		$ids = (array) explode(',', rtrim($newIdSequence, ','));

		// loop id's and set new sequence
		foreach($ids as $i => $id)
		{
			// build item
			$item['id'] = (int) $id;

			// change sequence
			$item['sequence'] = $i + 1;

			// update sequence
			BackendProjectsModel::updateImage($item);
		}

		// success output
		$this->output(self::OK, null, 'sequence updated');
	}
}

