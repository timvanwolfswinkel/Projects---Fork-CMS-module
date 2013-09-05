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
class FrontendProjectsModel implements FrontendTagsInterface
{
	/**
	 * Fetch a question
	 *
	 * @param string $url
	 * @return array
	 */
	public static function get($url)
	{
		return (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url, c.title AS category_title, m2.url AS category_url
			 FROM projects AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 INNER JOIN projects_categories AS c ON i.category_id = c.id
			 INNER JOIN meta AS m2 ON c.meta_id = m2.id
			 WHERE m.url = ? AND i.language = ? AND i.hidden = ?
			 ORDER BY i.sequence',
			array((string) $url, FRONTEND_LANGUAGE, 'N')
		);
	}

	/**
	 * Get all items in a category
	 *
	 * @param int $categoryId
	 * @param int[optional] $limit
	 * @param mixed[optional] $excludeIds
	 * @return array
	 */
	public static function getAllForCategory($categoryId, $limit = null, $excludeIds = null)
	{
		$categoryId = (int) $categoryId;
		$limit = (int) $limit;
		$excludeIds = (empty($excludeIds) ? array(0) : (array) $excludeIds);

		// get items
		if($limit != null) $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.*, m.url
			 FROM projects AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.category_id = ? AND i.language = ? AND i.hidden = ?
			 AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence
			 LIMIT ?',
			array((int) $categoryId, FRONTEND_LANGUAGE, 'N', (int) $limit)
		);

		else $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.*, m.url
			 FROM projects AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.category_id = ? AND i.language = ? AND i.hidden = ?
			 AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence',
			array((int) $categoryId, FRONTEND_LANGUAGE, 'N')
		);

		// init var
		$link = FrontendNavigation::getURLForBlock('projects', 'detail');

		// build the item urls and image
		foreach($items as &$item){
						
			// thumb
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM projects_images WHERE project_id = ? ORDER BY sequence', array((int)$item['id']));
			if($img) $item['image'] = FRONTEND_FILES_URL . '/projects/' . $item['id'] . '/128x128/' . $img['filename'];
			else $item['image'] = '/' . APPLICATION . '/modules/projects/layout/images/dummy.png';
			
			// link 
			$item['full_url'] = $link . '/' . $item['url'];
		}
		
		return $items;
	}

	/**
	 * Get all categories
	 *
	 * @return array
	 */
	public static function getCategories()
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.*, m.url
			 FROM projects_categories AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ?
			 ORDER BY i.sequence',
			array(FRONTEND_LANGUAGE)
		);

		// init var
		$link = FrontendNavigation::getURLForBlock('projects', 'category');

		// build the item url
		foreach($items as &$item) $item['full_url'] = $link . '/' . $item['url'];

		return $items;
	}

	/**
	 * Get all images for a projects
	 *
	 * @return array
	 */
	public static function getImages($id, $settings)
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.*
			 FROM projects_images AS i
			 WHERE i.project_id = ?
			 ORDER BY i.sequence',
			array((int)$id)
		);

		// init var
		$link = FrontendNavigation::getURLForBlock('projects', 'category');

		// build the item url
		foreach($items as &$item){
			$item['image_thumb'] = FRONTEND_FILES_URL . '/projects/' . $item['project_id'] . '/128x128/' . $item['filename'];
			$item['image_big'] = FRONTEND_FILES_URL . '/projects/' . $item['project_id'] . '/' . $settings["width2"] . 'x' . $settings["height2"] .  '/' . $item['filename'];
		}
		
		return $items;
	}

	/**
	 * Get a category
	 *
	 * @param string $url
	 * @return array
	 */
	public static function getCategory($url)
	{
		return (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url
			 FROM projects_categories AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE m.url = ? AND i.language = ?
			 ORDER BY i.sequence',
			array((string) $url, FRONTEND_LANGUAGE)
		);
	}

	/**
	 * Fetch the list of tags for a list of items
	 *
	 * @param array $ids
	 * @return array
	 */
	public static function getForTags(array $ids)
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.title AS title, m.url
			 FROM  projects AS i
			 INNER JOIN meta AS m ON m.id = i.meta_id
			 WHERE i.hidden = ? AND i.id IN (' . implode(',', $ids) . ')
			 ORDER BY i.title',
			array('N')
		);

		if(!empty($items))
		{
			$link = FrontendNavigation::getURLForBlock('projects', 'detail');

			// build the item urls
			foreach($items as &$row) $row['full_url'] = $link . '/' . $row['url'];
		}

		return $items;
	}

	/**
	 * Get the id of an item by the full URL of the current page.
	 * Selects the proper part of the full URL to get the item's id from the database.
	 *
	 * @param FrontendURL $url
	 * @return int
	 */
	public static function getIdForTags(FrontendURL $url)
	{
		$itemURL = (string) $url->getParameter(1);
		return self::get($itemURL);
	}

	/**
	 * Get related items based on tags
	 *
	 * @param int $id
	 * @param int[optional] $limit
	 * @return array
	 */
	public static function getSpotlightProject()
	{
		$item = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url, c.title AS category_title, m2.url AS category_url
			 FROM projects AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 INNER JOIN projects_categories AS c ON i.category_id = c.id
			 INNER JOIN meta AS m2 ON c.meta_id = m2.id
			 WHERE i.language = ? AND i.hidden = ? AND i.spotlight = ?
			 ORDER BY RAND()',
			array(FRONTEND_LANGUAGE, 'N', 'Y')
		);
		
		if($item){		
			$img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM projects_images WHERE project_id = ?', array((int)$item['id']));
			if($img) $item['image'] = FRONTEND_FILES_URL . '/projects/' . $item['id'] . '/128x128/' . $img['filename'];
			else $item['image'] = '/' . APPLICATION . '/modules/projects/layout/images/dummy.png';;
			$item['full_url'] = FrontendNavigation::getURLForBlock('projects', 'detail') . '/' . $item['url'];
		} 
			
		return $item;
	}
	
	/**
	 * Parse the search results for this module
	 *
	 * Note: a module's search function should always:
	 * 		- accept an array of entry id's
	 * 		- return only the entries that are allowed to be displayed, with their array's index being the entry's id
	 *
	 *
	 * @param array $ids
	 * @return array
	 */
	public static function search(array $ids)
	{
		$items = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT i.id, i.title, i.text AS text, m.url,
			 c.title AS category_title, m2.url AS category_url
			 FROM projects AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 INNER JOIN projects_categories AS c ON c.id = i.category_id
			 INNER JOIN meta AS m2 ON c.meta_id = m2.id
			 WHERE i.hidden = ? AND i.language = ? AND i.id IN (' . implode(',', $ids) . ')',
			array('N', FRONTEND_LANGUAGE),
			'id'
		);

		// prepare items for search
		foreach($items as &$item)
		{
			$item['full_url'] = FrontendNavigation::getURLForBlock('projects', 'detail') . '/' . $item['url'];
		}

		return $items;
	}
}
