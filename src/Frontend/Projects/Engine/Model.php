<?php

namespace Frontend\Modules\Projects\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Url as FrontendURL;
use Frontend\Modules\Tags\Engine\Model as FrontendTagsModel;
use Frontend\Modules\Tags\Engine\TagsInterface as FrontendTagsInterface;

/**
 * In this file we store all generic functions that we will be using in the projects module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Model implements FrontendTagsInterface
{
    /**
     * @param null $url
     * @return array
     */
    public static function get($url)
    {
        $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
              m.url, c.title AS category_title, mc.url AS category_url, cl.title AS client_title, mcl.url AS client_url
             FROM projects AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             INNER JOIN projects_clients AS cl ON i.client_id = cl.id
             INNER JOIN projects_categories AS c ON i.category_id = c.id
             INNER JOIN meta AS mc ON c.meta_id = mc.id
             INNER JOIN meta AS mcl ON cl.meta_id = mcl.id
             WHERE m.url = ? AND i.language = ? AND i.hidden = ?
             ORDER BY i.sequence',
            array((string)$url, FRONTEND_LANGUAGE, 'N')
        );

        if (!empty($item['id'])) {
            $images = self::getImages($item['id']);
            if (!empty($images)) {
                $item['images'] = $images;
            }
        }
        return $item;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getAll($limit = 10, $offset = 0)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
              m.url, c.title AS category_title, mc.url AS category_url, mcl.url AS client_url
             FROM projects AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             INNER JOIN projects_categories AS c ON i.category_id = c.id
             INNER JOIN meta AS mc ON c.meta_id = mc.id
             INNER JOIN projects_clients AS cl ON i.client_id = cl.id
             INNER JOIN meta AS mcl ON cl.meta_id = mcl.id
             WHERE i.language = ? AND hidden = ?
             ORDER BY i.sequence ASC
             LIMIT ?, ?',
            array(FRONTEND_LANGUAGE, 'N', (int)$offset, (int)$limit));

        // no results?
        if (empty($items)) return array();

        $detailUrl = FrontendNavigation::getURLForBlock('Projects', 'Detail');
        $categoryUrl = FrontendNavigation::getURLForBlock('Projects', 'Category');
        $clientUrl = FrontendNavigation::getURLForBlock('Projects', 'Client');

        // prepare items
        $numberOfImagesToShowInList = FrontendModel::getModuleSetting('Projects', 'overview_num_of_images', 1);
        foreach ($items as &$item) {
            $images = self::getImages($item['id'], $numberOfImagesToShowInList);
            if (!empty($images)) {
                $item['images'] = $images;
            }
            $item['full_url'] = $detailUrl . '/' . $item['url'];
            $item['category_full_url'] = $categoryUrl . '/' . $item['category_url'];
            $item['client_full_url'] = $clientUrl . '/' . $item['client_url'];
        }

        return $items;
    }

    /**
     * Get the number of items
     *
     * @return int
     */
    public static function getAllCount()
    {
        return (int)FrontendModel::getContainer()->get('database')->getVar(
            'SELECT COUNT(i.id) AS count
             FROM projects AS i
            WHERE i.language = ? AND hidden = ?',
            array(FRONTEND_LANGUAGE, 'N')
        );
    }

    /**
     * @param array $Ids
     * @return array
     */
    public static function getProjectsByIds(array $Ids)
    {
        if (empty($Ids)) {
            return array();
        }
        /** @var $db SpoonDatabase */
        $db = FrontendModel::getContainer()->get('database');
        $items = (array)$db->getRecords(
            'SELECT i.*, m.url, c.title AS category_title, mc.url AS category_url, mcl.url AS client_url
             FROM projects AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             INNER JOIN projects_categories AS c ON i.category_id = c.id
             INNER JOIN meta AS mc ON c.meta_id = mc.id
             INNER JOIN projects_clients AS cl ON i.client_id = cl.id
             INNER JOIN meta AS mcl ON cl.meta_id = mcl.id
             WHERE i.language = ? AND hidden = ? AND i.id IN (' . implode(',', $Ids) . ')
             ORDER BY i.sequence ASC',
            array(FRONTEND_LANGUAGE, 'N'));

        // no results?
        if (empty($items)) return array();

        // get detail action url
        $detailUrl = FrontendNavigation::getURLForBlock('Projects', 'Detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('Projects', 'Category');

        // get client action url
        $clientUrl = FrontendNavigation::getURLForBlock('Projects', 'Client');

        // prepare items
        $numberOfImagesToShowInList = FrontendModel::getModuleSetting('Projects', 'overview_num_of_images', 1);
        foreach ($items as &$item) {
            $images = self::getImages($item['id'], $numberOfImagesToShowInList);
            if (!empty($images)) {
                $item['images'] = $images;
            }
            $item['full_url'] = $detailUrl . '/' . $item['url'];
            $item['category_full_url'] = $categoryUrl . '/' . $item['category_url'];
            $item['client_full_url'] = $clientUrl . '/' . $item['client_url'];
        }

        // return
        return $items;
    }

    /**
     * @param $categoryId
     * @param null $limit
     * @param null $excludeIds
     * @return array
     */
    public static function getAllForCategory($categoryId, $limit = null, $excludeIds = null)
    {
        $categoryId = (int)$categoryId;
        $limit = (int)$limit;
        $excludeIds = (empty($excludeIds) ? array(0) : (array)$excludeIds);

        // get items
        if ($limit != null) {
            $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
                 m.url, c.title AS category_title, mc.url AS category_url
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN projects_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS mc ON c.meta_id = mc.id
                 WHERE i.category_id = ? AND i.language = ? AND i.hidden = ?
                 AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence
			 LIMIT ?',
                array((int)$categoryId, FRONTEND_LANGUAGE, 'N', (int)$limit)
            );
        } else {
            $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
                 m.url, c.title AS category_title, mc.url AS category_url
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN projects_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS mc ON c.meta_id = mc.id
                 WHERE i.category_id = ? AND i.language = ? AND i.hidden = ?
                 AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence',
                array((int)$categoryId, FRONTEND_LANGUAGE, 'N')
            );
        }

        // init var
        $link = FrontendNavigation::getURLForBlock('Projects', 'Detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('Projects', 'Category');

        // build the item urls and image
        $numberOfImagesToShowInList = FrontendModel::getModuleSetting('Projects', 'overview_num_of_images', 1);
        foreach ($items as &$item) {
            $images = self::getImages($item['id'], $numberOfImagesToShowInList);
            if (!empty($images)) {
                $item['images'] = $images;
            }
            $item['category_full_url'] = $categoryUrl . '/' . $item['category_url'];
            $item['full_url'] = $link . '/' . $item['url'];
        }

        return $items;
    }

    /**
     * Get all items in a client
     *
     * @param int $clientId
     * @param int [optional] $limit
     * @param mixed [optional] $excludeIds
     * @return array
     */
    public static function getAllForClient($clientId, $limit = null, $excludeIds = null)
    {
        $clientId = (int)$clientId;
        $limit = (int)$limit;
        $excludeIds = (empty($excludeIds) ? array(0) : (array)$excludeIds);

        // get items
        if ($limit != null) $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on, m.url
             FROM projects AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE i.client_id = ? AND i.language = ? AND i.hidden = ?
             AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence
			 LIMIT ?',
            array((int)$clientId, FRONTEND_LANGUAGE, 'N', (int)$limit)
        );

        else $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on, m.url
             FROM projects AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE i.client_id = ? AND i.language = ? AND i.hidden = ?
             AND i.id NOT IN (' . implode(',', $excludeIds) . ')
			 ORDER BY i.sequence',
            array((int)$clientId, FRONTEND_LANGUAGE, 'N')
        );

        // init var
        $link = FrontendNavigation::getURLForBlock('Projects', 'Detail');

        // build the item urls and image
        $numberOfImagesToShowInList = FrontendModel::getModuleSetting('Projects', 'overview_num_of_images', 1);
        foreach ($items as &$item) {
            $images = self::getImages($item['id'], $numberOfImagesToShowInList);
            if (!empty($images)) {
                $item['images'] = $images;
            }
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
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, m.url
             FROM projects_categories AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE i.language = ?
             ORDER BY i.sequence',
            array(FRONTEND_LANGUAGE)
        );

        $link = FrontendNavigation::getURLForBlock('Projects', 'Category');

        // build the item url
        foreach ($items as &$item) $item['full_url'] = $link . '/' . $item['url'];

        return $items;
    }

    /**
     * Get all clients
     *
     * @return array
     */
    public static function getClients()
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, m.url
             FROM projects_clients AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE i.language = ?
             ORDER BY i.sequence',
            array(FRONTEND_LANGUAGE)
        );

        //die(print_r($items));
        
        $link = FrontendNavigation::getURLForBlock('Projects', 'Client');

        // build the item urls and image
        foreach ($items as &$item) {
            // thumb
            if($item['image'] != null){
                $item['image'] = FRONTEND_FILES_URL . '/Projects/references/300x200/' . $item['image']; 
            }
            
            $item['full_url'] = $link . '/' . $item['url'];   
        }

        return $items;
    }

    /**
     * Get all images for a project
     *
     * @param $id
     * @param null $limit
     * @return array
     */
    public static function getImages($id, $limit = null)
    {
        $settings = FrontendModel::getModuleSettings('Projects');

        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
             FROM projects_images AS i
             WHERE i.project_id = ?
             ORDER BY i.sequence' . (!empty($limit) ? ' LIMIT ' . $limit : ''),
            array((int)$id)
        );

        $imageFormats = array();
        if (isset($settings['width1'], $settings['height1'])) {
            $imageFormats['small'] = $settings['width1'] . 'x' . $settings['height1'];
        }
        if (isset($settings['width2'], $settings['height2'])) {
            $imageFormats['medium'] = $settings['width2'] . 'x' . $settings['height2'];
        }
        if (isset($settings['width3'], $settings['height3'])) {
            $imageFormats['large'] = $settings['width3'] . 'x' . $settings['height3'];
        }

        // build the item url
        foreach ($items as &$item) {
            foreach ($imageFormats as $format_name => $format) {
                $item['sizes'][$format_name] = FRONTEND_FILES_URL . '/Projects/' . $item['project_id'] . '/' . $format . '/' . $item['filename'];
            }
        }

        return $items;
    }

    /**
     * Get all videos for a project
     *
     * @param $id
     * @return array
     */
    public static function getVideos($id)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
             FROM projects_videos AS i
             WHERE i.project_id = ?
             ORDER BY i.sequence',
            array((int)$id)
        );

        // build the image thumbnail for youtube/vimeo
        foreach ($items as &$item) {
            // YOUTUBE
            if (strpos($item['embedded_url'], 'youtube') !== false) {
                $ytQuery = parse_url($item['embedded_url'], PHP_URL_QUERY);
                parse_str($ytQuery, $ytData);

                if (isset($ytData['v'])) {
                    $item['video_id'] = $ytData['v'];
                    $item['url'] = "http://www.youtube.com/v/" . $ytData['v'] . "?fs=1&amp;autoplay=1";
                    $item['image'] = "http://i3.ytimg.com/vi/" . $ytData['v'] . "/default.jpg";
                }
                // VIMEO
            } else if (strpos($item['embedded_url'], 'vimeo') !== false) {
                $vmLink = str_replace('http://vimeo.com/', 'http://vimeo.com/api/v2/video/', $item['embedded_url']) . '.php';
                $vmData = unserialize(file_get_contents($vmLink));

                if (isset($vmData[0]['id'])) {
                    $item['video_id'] = $vmData[0]['id'];;
                    $item['url'] = "http://player.vimeo.com/video/" . $vmData[0]['id'] . "?autoplay=1";
                    $item['image'] = $vmData[0]['thumbnail_small'];;
                }
            } else {
                // NO YOUTUBE OR VIMEO URL GIVEN..
            }
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
        return (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, m.url
             FROM projects_categories AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE m.url = ? AND i.language = ?
             ORDER BY i.sequence',
            array((string)$url, FRONTEND_LANGUAGE)
        );
    }

    /**
     * Get a client
     * @param string $url
     * @return array
     */
    public static function getClient($url)
    {
        $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, m.url
             FROM projects_clients AS i
             INNER JOIN meta AS m ON i.meta_id = m.id
             WHERE m.url = ? AND i.language = ?
             ORDER BY i.sequence',
            array((string)$url, FRONTEND_LANGUAGE)
        );

        // build up the item
        if($item['image'] != null){
            $item['image'] = FRONTEND_FILES_URL . '/Projects/references/300x200/' . $item['image']; 
        }
        $link = FrontendNavigation::getURLForBlock('Projects', 'Client');
        $item['full_url'] = $link . '/' . $item['url'];

        return $item;
    }

    /**
     * Get related projects
     *
     * @param int $id
     * @return array
     */
    public static function getRelatedProjects($id)
    {
        $relatedIds = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
             FROM projects_related AS i
             WHERE i.project_id = ?',
            array((int)$id)
        );

        if (empty($relatedIds)) {
            return array();
        }
        $relatedProjects = array();

        foreach ($relatedIds as $relatedProject) {
            $relatedProjects[] = self::get(null, $relatedProject['related_project_id']);
        }

        return $relatedProjects;
    }

    /**
     * Fetch the list of tags for a list of items
     *
     * @param array $ids
     * @return array
     */
    public static function getForTags(array $ids)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.title AS title, m.url
             FROM  projects AS i
             INNER JOIN meta AS m ON m.id = i.meta_id
             WHERE i.hidden = ? AND i.id IN (' . implode(',', $ids) . ')
			 ORDER BY i.title',
            array('N')
        );

        if (!empty($items)) {
            $link = FrontendNavigation::getURLForBlock('Projects', 'Detail');

            // build the item urls
            foreach ($items as &$row) $row['full_url'] = $link . '/' . $row['url'];
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
        $itemURL = (string)$url->getParameter(1);
        return self::get($itemURL);
    }

    /**
     * @param null $url
     * @return array
     */
    public static function getSpotlightProject($url = null)
    {
        if ($url) {
            $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
                'SELECT i.*, m.url, c.title AS category_title, m2.url AS category_url
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN projects_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS m2 ON c.meta_id = m2.id
                 WHERE i.language = ? AND i.hidden = ? AND m.url = ?
                 ORDER BY RAND()',
                array(FRONTEND_LANGUAGE, 'N', $url)
            );
        } else {
            $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
                'SELECT i.*, m.url, c.title AS category_title, m2.url AS category_url
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 INNER JOIN projects_categories AS c ON i.category_id = c.id
                 INNER JOIN meta AS m2 ON c.meta_id = m2.id
                 WHERE i.language = ? AND i.hidden = ? AND i.spotlight = ?
                 ORDER BY RAND()',
                array(FRONTEND_LANGUAGE, 'N', 'Y')
            );
        }

        if ($item) {
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT filename FROM projects_images WHERE project_id = ? ORDER BY sequence LIMIT 1', array((int)$item['id']));
            if ($img) {
                $item['image'] = FRONTEND_FILES_URL . '/Projects/' . $item['id'] . '/295x195/' . $img['filename'];
                $item['image_header'] = FRONTEND_FILES_URL . '/Projects/' . $item['id'] . '/1920x456/' . $img['filename'];
            };
            $item['full_url'] = FrontendNavigation::getURLForBlock('Projects', 'Detail') . '/' . $item['url'];
        }

        return $item;
    }

    /**
     * Parse the search results for this module
     *
     * Note: a module's search function should always:
     *        - accept an array of entry id's
     *        - return only the entries that are allowed to be displayed, with their array's index being the entry's id
     *
     *
     * @param array $ids
     * @return array
     */
    public static function search(array $ids)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
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
        foreach ($items as &$item) {
            $item['full_url'] = FrontendNavigation::getURLForBlock('Projects', 'Detail') . '/' . $item['url'];
        }

        return $items;
    }
}
