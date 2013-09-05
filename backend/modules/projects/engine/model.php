<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the projects module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class BackendProjectsModel
{
	const QRY_DATAGRID_BROWSE =
		'SELECT i.id, i.category_id, i.title, i.hidden, i.sequence
		 FROM projects AS i
		 WHERE i.language = ? AND i.category_id = ?
		 ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_CATEGORIES =
		'SELECT i.id, i.title, COUNT(p.id) AS num_items, i.sequence
		 FROM projects_categories AS i
		 LEFT OUTER JOIN projects AS p ON i.id = p.category_id AND p.language = i.language
		 WHERE i.language = ?
		 GROUP BY i.id
		 ORDER BY i.sequence ASC';

	const QRY_DATAGRID_BROWSE_IMAGES =
		'SELECT i.id, i.project_id, i.filename, i.title, i.sequence
		 FROM projects_images AS i
		 WHERE i.project_id = ?
		 GROUP BY i.id';
	
	/**
	 * Delete a question
	 *
	 * @param int $id
	 */
	public static function delete($id)
	{
		BackendModel::getContainer()->get('database')->delete('projects', 'id = ?', array((int) $id));
		BackendTagsModel::saveTags($id, '', 'projects');
	}

	/**
	 * Delete a specific category
	 *
	 * @param int $id
	 */
	public static function deleteCategory($id)
	{
		$db = BackendModel::getContainer()->get('database');
		$item = self::getCategory($id);

		if(!empty($item))
		{
			$db->delete('meta', 'id = ?', array($item['meta_id']));
			$db->delete('projects_categories', 'id = ?', array((int) $id));
			$db->update('projects', array('category_id' => null), 'category_id = ?', array((int) $id));

			// invalidate the cache for the projects
			BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
		}
	}

	/**
	 * Is the deletion of a category allowed?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function deleteCategoryAllowed($id)
	{
		// get result
		$result = (BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM projects AS i
			 WHERE i.category_id = ? AND i.language = ?
			 LIMIT 1',
			 array((int) $id, BL::getWorkingLanguage())) == 0);

		// exception
		if(!BackendModel::getModuleSetting('projects', 'allow_multiple_categories', true) && self::getCategoryCount() == 1)
		{
			return false;
		}

		else return $result;
	}

	/**
	 * @param array $ids
	 */
	public static function deleteImage(array $ids)
	{
		if(empty($ids)) return;

		foreach($ids as $id)
		{
			$item = self::getImage($id);
			$project = self::get($item['project_id']);

			// delete image reference from db
			BackendModel::getContainer()->get('database')->delete('projects_images', 'id = ?', array($id));

			// delete image from disk
			$basePath = FRONTEND_FILES_PATH . '/projects/' . $item['project_id'];
			SpoonFile::delete($basePath . '/source/' . $item['filename']);
			SpoonFile::delete($basePath . '/64x64/' . $item['filename']);
			SpoonFile::delete($basePath . '/128x128/' . $item['filename']);
			SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width1') . 'x' . BackendModel::getModuleSetting('projects', 'height1') . '/' . $item['filename']);
			SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width2') . 'x' . BackendModel::getModuleSetting('projects', 'height2') . '/' . $item['filename']);
			SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width3') . 'x' . BackendModel::getModuleSetting('projects', 'height3') . '/' . $item['filename']);
		}

		BackendModel::invalidateFrontendCache('slideshowCache');
	}

	/**
	 * Does the question exist?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
		 	 FROM projects AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1',
			 array((int) $id, BL::getWorkingLanguage()));
	}

	/**
	 * Does the category exist?
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function existsCategory($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM projects_categories AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1',
			 array((int) $id, BL::getWorkingLanguage()));
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public static function existsImage($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM projects_images AS a
			 WHERE a.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Fetch a project
	 *
	 * @param int $id
	 * @return array
	 */
	public static function get($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url
			 FROM projects AS i
			 INNER JOIN meta AS m ON m.id = i.meta_id
			 WHERE i.id = ? AND i.language = ?',
			 array((int) $id, BL::getWorkingLanguage()));
	}

	/**
	 * Fetch an image
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getImage($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*
			 FROM projects_images AS i
			 WHERE i.id = ?',
			 array((int) $id));
	}

	/**
	 * Get all items by a given tag id
	 *
	 * @param int $tagId
	 * @return array
	 */
	public static function getByTag($tagId)
	{
		$items = (array) BackendModel::getContainer()->get('database')->getRecords(
			'SELECT i.id AS url, i.question, mt.module
			 FROM modules_tags AS mt
			 INNER JOIN tags AS t ON mt.tag_id = t.id
			 INNER JOIN projects AS i ON mt.other_id = i.id
			 WHERE mt.module = ? AND mt.tag_id = ? AND i.language = ?',
			 array('projects', (int) $tagId, BL::getWorkingLanguage()));

		foreach($items as &$row)
		{
			$row['url'] = BackendModel::createURLForAction('edit', 'projects', null, array('id' => $row['url']));
		}

		return $items;
	}

	/**
	 * Get all the categories
	 *
	 * @param bool[optional] $includeCount
	 * @return array
	 */
	public static function getCategories($includeCount = false)
	{
		$db = BackendModel::getContainer()->get('database');

		if($includeCount)
		{
			return (array) $db->getPairs(
				'SELECT i.id, CONCAT(i.title, " (",  COUNT(p.category_id) ,")") AS title
				 FROM projects_categories AS i
				 LEFT OUTER JOIN projects AS p ON i.id = p.category_id AND i.language = p.language
				 WHERE i.language = ?
				 GROUP BY i.id
				 ORDER BY i.sequence',
				 array(BL::getWorkingLanguage()));
		}

		return (array) $db->getPairs(
			'SELECT i.id, i.title
			 FROM projects_categories AS i
			 WHERE i.language = ?
			 ORDER BY i.sequence',
			 array(BL::getWorkingLanguage()));
	}

	/**
	 * Fetch a category
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getCategory($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*
			 FROM projects_categories AS i
			 WHERE i.id = ? AND i.language = ?',
			 array((int) $id, BL::getWorkingLanguage()));
	}

	/**
	 * Fetch the category count
	 *
	 * @return int
	 */
	public static function getCategoryCount()
	{
		return (int) BackendModel::getContainer()->get('database')->getVar(
			'SELECT COUNT(i.id)
			 FROM projects_categories AS i
			 WHERE i.language = ?',
			 array(BL::getWorkingLanguage()));
	}

	/**
	 * Fetch the feedback item
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getFeedback($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT f.*
			 FROM projects_feedback AS f
			 WHERE f.id = ?',
			 array((int) $id));
	}

	/**
	 * Get the maximum sequence for a category
	 *
	 * @return int
	 */
	public static function getMaximumCategorySequence()
	{
		return (int) BackendModel::getContainer()->get('database')->getVar(
			'SELECT MAX(i.sequence)
			 FROM projects_categories AS i
			 WHERE i.language = ?',
			 array(BL::getWorkingLanguage()));
	}

	/**
	 * Get the max sequence id for a category
	 *
	 * @param int $id		The category id.
	 * @return int
	 */
	public static function getMaximumSequence($id)
	{
		return (int) BackendModel::getContainer()->get('database')->getVar(
			'SELECT MAX(i.sequence)
			 FROM projects AS i
			 WHERE i.category_id = ?',
			 array((int) $id));
	}

	/**
	 * Get the max sequence id for an image
	 *
	 * @param int $id		The project id.
	 * @return int
	 */
	public static function getMaximumImagesSequence($id)
	{
		return (int) BackendModel::getContainer()->get('database')->getVar(
			'SELECT MAX(i.sequence)
			 FROM projects_images AS i
			 WHERE i.project_id = ?',
			 array((int) $id));
	}

	/**
	 * Retrieve the unique URL for an item
	 *
	 * @param string $url
	 * @param int[optional] $id	The id of the item to ignore.
	 * @return string
	 */
	public static function getURL($url, $id = null)
	{
		$url = SpoonFilter::urlise((string) $url);
		$db = BackendModel::getContainer()->get('database');

		// new item
		if($id === null)
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM projects AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $url)))
			{
				$url = BackendModel::addNumber($url);
				return self::getURL($url);
			}
		}
		// current category should be excluded
		else
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM projects AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $url, $id)))
			{
				$url = BackendModel::addNumber($url);
				return self::getURL($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Retrieve the unique URL for a category
	 *
	 * @param string $url
	 * @param int[optional] $id The id of the category to ignore.
	 * @return string
	 */
	public static function getURLForCategory($url, $id = null)
	{
		$url = SpoonFilter::urlise((string) $url);
		$db = BackendModel::getContainer()->get('database');

		// new category
		if($id === null)
		{
			if((bool) $db->getVar(
				'SELECT 1
				 FROM projects_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $url)))
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url);
			}
		}
		// current category should be excluded
		else
		{
			if((bool) $db->getVar(
				'SELECT 1
				 FROM projects_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $url, $id)))
			{
				$url = BackendModel::addNumber($url);
				return self::getURLForCategory($url, $id);
			}
		}

		return $url;
	}

	/**
	 * Insert a question in the database
	 *
	 * @param array $item
	 * @return int
	 */
	public static function insert(array $item)
	{
		$insertId = BackendModel::getContainer()->get('database')->insert('projects', $item);

		BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());

		return $insertId;
	}

	/**
	 * Insert a category in the database
	 *
	 * @param array $item
	 * @param array[optional] $meta The metadata for the category to insert.
	 * @return int
	 */
	public static function insertCategory(array $item, $meta = null)
	{
		$db = BackendModel::getContainer()->get('database');

		if($meta !== null) $item['meta_id'] = $db->insert('meta', $meta);
		$item['id'] = $db->insert('projects_categories', $item);

		BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());

		return $item['id'];
	}

	/**
	 * @param string $item
	 * @return int
	 */
	private static function insertImage($item)
	{
		return (int) BackendModel::getContainer()->get('database')->insert('projects_images', $item);
	}

	/**
	 * Update a certain question
	 *
	 * @param array $item
	 */
	public static function update(array $item)
	{
		BackendModel::getContainer()->get('database')->update('projects', $item, 'id = ?', array((int) $item['id']));
		BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
	}

	/**
	 * Update a certain category
	 *
	 * @param array $item
	 */
	public static function updateCategory(array $item)
	{
		BackendModel::getContainer()->get('database')->update('projects_categories', $item, 'id = ?', array($item['id']));
		BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
	}

	/**
	 * @param array $item
	 * @return int
	 */
	public static function updateImage(array $item)
	{
		BackendModel::invalidateFrontendCache('projectsCache');
		return (int) BackendModel::getContainer()->get('database')->update(
			'projects_images',
			$item,
			'id = ?',
			array($item['id'])
		);
	}

	/**
	 * @param array $item
	 * @return int
	 */
	public static function saveImage(array $item)
	{
		if(isset($item['id']) && self::existsImage($item['id']))
		{
			self::updateImage($item);
		}
		else
		{
			$item['id'] = self::insertImage($item);
		}

		BackendModel::invalidateFrontendCache('projectsCache');
		return (int) $item['id'];
	}
}
