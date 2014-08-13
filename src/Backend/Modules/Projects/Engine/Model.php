<?php

namespace Backend\Modules\Projects\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Exception;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;

/**
 * In this file we store all generic functions that we will be using in the projects module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class Model
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, i.category_id, i.title, p.title AS client, i.client_id, i.hidden, i.sequence
         FROM projects AS i
         LEFT OUTER JOIN projects_clients AS p ON p.id = i.client_id
         WHERE i.language = ? AND i.category_id = ?
         ORDER BY i.sequence ASC';

    const QRY_DATAGRID_BROWSE_CATEGORIES =
        'SELECT i.id, i.title, COUNT(p.id) AS num_items, i.sequence
         FROM projects_categories AS i
         LEFT OUTER JOIN projects AS p ON i.id = p.category_id AND p.language = i.language
         WHERE i.language = ?
         GROUP BY i.id
         ORDER BY i.sequence ASC';

    const QRY_DATAGRID_BROWSE_CLIENTS =
        'SELECT i.id, i.title, i.sequence
         FROM projects_clients AS i
         WHERE i.language = ?
         GROUP BY i.id
         ORDER BY i.sequence ASC';

    const QRY_DATAGRID_BROWSE_IMAGES =
        'SELECT i.id, i.project_id, i.filename, i.title, i.sequence
         FROM projects_images AS i
         WHERE i.project_id = ?
         GROUP BY i.id';

    const QRY_DATAGRID_BROWSE_FILES =
        'SELECT i.id, i.project_id, i.filename, i.title, i.sequence
         FROM projects_files AS i
         WHERE i.project_id = ?
         GROUP BY i.id';

    const QRY_DATAGRID_BROWSE_VIDEOS =
        'SELECT i.id, i.project_id, i.embedded_url, i.title, i.sequence
         FROM projects_videos AS i
         WHERE i.project_id = ?
         GROUP BY i.id';

    /**
     * Delete a question
     *
     * @param int $id
     */
    public static function delete($id)
    {
        $id = (int)$id;
        $projectFilesPath = FRONTEND_FILES_PATH . '/projects/' . $id;
        \SpoonDirectory::delete($projectFilesPath);
        $project = self::get($id);
        if (!empty($project)) {
            $database = BackendModel::getContainer()->get('database');
            $database->delete('meta', 'id = ?', array((int)$project['meta_id']));
            $database->delete('projects_related', 'project_id = ? OR related_project_id = ?', array($id, $id));
            $database->delete('projects_images', 'project_id = ?', array($id));
            $database->delete('projects_videos', 'project_id = ?', array($id));
            $database->delete('projects_files', 'project_id = ?', array($id));
            $database->delete('projects', 'id = ?', array($id));
            BackendTagsModel::saveTags($id, '', 'projects');
        }
    }

    /**
     * Delete a specific category
     *
     * @param int $id
     */
    public static function deleteCategory($id)
    {
        $id = (int)$id;

        $db = BackendModel::getContainer()->get('database');
        $item = self::getCategory($id);

        // build extra
        $extra = array('id' => $item['extra_id'],
            'module' => 'projects',
            'type' => 'widget',
            'action' => 'category');

        // delete extra
        $db->delete('modules_extras', 'id = ? AND module = ? AND type = ? AND action = ?', array($extra['id'], $extra['module'], $extra['type'], $extra['action']));

        // delete blocks with this item linked
        $db->delete('pages_blocks', 'extra_id = ?', array($item['extra_id']));

        if (!empty($item)) {
            $db->delete('meta', 'id = ?', array($item['meta_id']));
            $db->delete('projects_categories', 'id = ?', array((int)$id));
            $db->update('projects', array('category_id' => null), 'category_id = ?', array($id));

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
                array((int)$id, BL::getWorkingLanguage())) == 0);

        // exception
        if (!BackendModel::getModuleSetting('projects', 'allow_multiple_categories', true) && self::getCategoryCount() == 1) {
            return false;
        } else return $result;
    }

    /**
     * Delete a specific client
     *
     * @param int $id
     */
    public static function deleteClient($id)
    {
        $db = BackendModel::getContainer()->get('database');
        $item = self::getClient($id);

        if (!empty($item)) {
            $db->delete('meta', 'id = ?', array($item['meta_id']));
            $db->delete('projects_clients', 'id = ?', array((int)$id));
            $db->update('projects', array('client_id' => null), 'client_id = ?', array((int)$id));

            // invalidate the cache for the projects
            BackendModel::invalidateFrontendCache('Projects', BL::getWorkingLanguage());
        }
    }

    /**
     * Is the deletion of a client allowed?
     *
     * @param int $id
     * @return bool
     */
    public static function deleteClientAllowed($id)
    {
        // get result
        $result = (BackendModel::getContainer()->get('database')->getVar(
                'SELECT 1
                 FROM projects AS i
                 WHERE i.client_id = ? AND i.language = ?
                 LIMIT 1',
                array((int)$id, BL::getWorkingLanguage())) == 0);

        // exception
        if (!BackendModel::getModuleSetting('projects', 'allow_multiple_clients', true) && self::getClientCount() == 1) {
            return false;
        } else return $result;
    }

    /**
     * @param array $ids
     */
    public static function deleteImage(array $ids)
    {
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $item = self::getImage($id);
            $project = self::get($item['project_id']);

            // delete image reference from db
            BackendModel::getContainer()->get('database')->delete('projects_images', 'id = ?', array($id));

            // delete image from disk
            $basePath = FRONTEND_FILES_PATH . '/projects/' . $item['project_id'];
            \SpoonFile::delete($basePath . '/source/' . $item['filename']);
            \SpoonFile::delete($basePath . '/64x64/' . $item['filename']);
            \SpoonFile::delete($basePath . '/128x128/' . $item['filename']);
            \SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width1') . 'x' . BackendModel::getModuleSetting('projects', 'height1') . '/' . $item['filename']);
            \SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width2') . 'x' . BackendModel::getModuleSetting('projects', 'height2') . '/' . $item['filename']);
            \SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('projects', 'width3') . 'x' . BackendModel::getModuleSetting('projects', 'height3') . '/' . $item['filename']);
        }

        BackendModel::invalidateFrontendCache('slideshowCache');
    }

    /**
     * @param array $ids
     */
    public static function deleteFile(array $ids)
    {
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $item = self::getFile($id);
            $project = self::get($item['project_id']);

            // delete file reference from db
            BackendModel::getContainer()->get('database')->delete('projects_files', 'id = ?', array($id));

            // delete file from disk
            $basePath = FRONTEND_FILES_PATH . '/projects/' . $item['project_id'];
            \SpoonFile::delete($basePath . '/source/' . $item['filename']);
        }
    }

    /**
     * @param array $ids
     */
    public static function deleteVideo(array $ids)
    {
        if (empty($ids)) return;

        foreach ($ids as $id) {
            $item = self::getVideo($id);
            $project = self::get($item['project_id']);

            // delete video reference from db
            BackendModel::getContainer()->get('database')->delete('projects_videos', 'id = ?', array($id));
        }
    }

    /**
     * Delete related project
     *
     * @param int The project id
     * @param int The related project id
     */
    public static function deleteRelatedProject($projectId, $relatedProjectId = null)
    {
        if (isset($relatedProjectId)) {
            BackendModel::getContainer()->get('database')->delete('projects_related', 'project_id = ? AND related_project_id = ?', array((int)$projectId, (int)$relatedProjectId));
        } else {
            BackendModel::getContainer()->get('database')->delete('projects_related', 'project_id = ?', array((int)$projectId));
        }
    }

    /**
     * Does the question exist?
     *
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
              FROM projects AS i
             WHERE i.id = ? AND i.language = ?
             LIMIT 1',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Does the category exist?
     *
     * @param int $id
     * @return bool
     */
    public static function existsCategory($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM projects_categories AS i
             WHERE i.id = ? AND i.language = ?
             LIMIT 1',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Does the client exist?
     *
     * @param int $id
     * @return bool
     */
    public static function existsClient($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM projects_clients AS i
             WHERE i.id = ? AND i.language = ?
             LIMIT 1',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsImage($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM projects_images AS a
             WHERE a.id = ?',
            array((int)$id)
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsFile($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM projects_files AS a
             WHERE a.id = ?',
            array((int)$id)
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsVideo($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM projects_videos AS a
             WHERE a.id = ?',
            array((int)$id)
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
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, m.url, UNIX_TIMESTAMP(i.date) AS date
             FROM projects AS i
             INNER JOIN meta AS m ON m.id = i.meta_id
             WHERE i.id = ? AND i.language = ?',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Get all projects grouped by categories
     *
     * @return array
     */
    public static function getAllProjectsGroupedByCategories()
    {
        $db = BackendModel::getContainer()->get('database');

        $allProjects = (array)$db->getRecords(
            'SELECT p.id, p.title, pc.id AS category_id, pc.title AS category_title
             FROM projects p
             INNER JOIN projects_categories pc ON p.category_id = pc.id
             WHERE p.language = ?',
            array(BL::getWorkingLanguage()));

        $projectsGroupedByCategory = array();

        foreach ($allProjects as $pid => $project) {
            $projectsGroupedByCategory[$project['category_title']][$project['id']] = $project['title'];
        }

        //die(print_r($projectsGroupedByCategory));

        return $projectsGroupedByCategory;
    }

    /**
     * Get related projects of an item
     *
     * @param int $id The project id
     * @return array
     */
    public static function getRelatedProjects($id)
    {
        $db = BackendModel::getContainer()->get('database');

        $relatedProjects = (array)$db->getPairs(
            'SELECT r.related_project_id AS keyId, r.related_project_id AS valueId
             FROM projects_related r
             WHERE r.project_id = ?',
            array((int)$id));

        // build new keys (starting from zero)
        $i = 0;

        foreach ($relatedProjects as $key => $value) {
            if (isset($relatedProjects[$key])) {
                $relatedProjects[$i] = $relatedProjects[$key];
                unset($relatedProjects[$key]);
            }
            $i++;
        }

        return $relatedProjects;
    }

    /**
     * Fetch an image
     *
     * @param int $id
     * @return array
     */
    public static function getImage($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
             FROM projects_images AS i
             WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Fetch an file
     *
     * @param int $id
     * @return array
     */
    public static function getFile($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
             FROM projects_files AS i
             WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Fetch an video
     *
     * @param int $id
     * @return array
     */
    public static function getVideo($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
             FROM projects_videos AS i
             WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Get all items by a given tag id
     *
     * @param int $tagId
     * @return array
     */
    public static function getByTag($tagId)
    {
        $items = (array)BackendModel::getContainer()->get('database')->getRecords(
            'SELECT i.id AS url, i.title AS name, mt.module
             FROM modules_tags AS mt
             INNER JOIN tags AS t ON mt.tag_id = t.id
             INNER JOIN projects AS i ON mt.other_id = i.id
             WHERE mt.module = ? AND mt.tag_id = ? AND i.language = ?',
            array('projects', (int)$tagId, BL::getWorkingLanguage()));

        foreach ($items as &$row) {
            $row['url'] = BackendModel::createURLForAction('edit', 'projects', null, array('id' => $row['url']));
        }

        return $items;
    }

    /**
     * Get all the categories
     *
     * @param bool [optional] $includeCount
     * @return array
     */
    public static function getCategories($includeCount = false)
    {
        $db = BackendModel::getContainer()->get('database');

        if ($includeCount) {
            return (array)$db->getPairs(
                'SELECT i.id, CONCAT(i.title, " (",  COUNT(p.category_id) ,")") AS title
                 FROM projects_categories AS i
                 LEFT OUTER JOIN projects AS p ON i.id = p.category_id AND i.language = p.language
                 WHERE i.language = ?
                 GROUP BY i.id
                 ORDER BY i.sequence',
                array(BL::getWorkingLanguage()));
        }

        return (array)$db->getPairs(
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
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
             FROM projects_categories AS i
             WHERE i.id = ? AND i.language = ?',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Fetch the category count
     *
     * @return int
     */
    public static function getCategoryCount()
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT COUNT(i.id)
             FROM projects_categories AS i
             WHERE i.language = ?',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Get all the clients
     *
     * @param bool [optional] $includeCount
     * @return array
     */
    public static function getClients($includeCount = false)
    {
        $db = BackendModel::getContainer()->get('database');

        if ($includeCount) {
            return (array)$db->getPairs(
                'SELECT i.id, CONCAT(i.title, " (",  COUNT(p.category_id) ,")") AS title
                 FROM projects_clients AS i
                 LEFT OUTER JOIN projects AS p ON i.id = p.client_id AND i.language = p.language
                 WHERE i.language = ?
                 GROUP BY i.id
                 ORDER BY i.sequence',
                array(BL::getWorkingLanguage()));
        }

        return (array)$db->getPairs(
            'SELECT i.id, i.title
             FROM projects_clients AS i
             WHERE i.language = ?
             ORDER BY i.sequence',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Fetch a client
     *
     * @param int $id
     * @return array
     */
    public static function getClient($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
             FROM projects_clients AS i
             WHERE i.id = ? AND i.language = ?',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Fetch the client count
     *
     * @return int
     */
    public static function getClientCount()
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT COUNT(i.id)
             FROM projects_clients AS i
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
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT f.*
             FROM projects_feedback AS f
             WHERE f.id = ?',
            array((int)$id));
    }

    /**
     * Get the maximum sequence for a category
     *
     * @return int
     */
    public static function getMaximumCategorySequence()
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects_categories AS i
             WHERE i.language = ?',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Get the maximum sequence for a client
     *
     * @return int
     */
    public static function getMaximumClientSequence()
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects_clients AS i
             WHERE i.language = ?',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Get the max sequence id for a category
     *
     * @param int $id The category id.
     * @return int
     */
    public static function getMaximumSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects AS i
             WHERE i.category_id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an image
     *
     * @param int $id The project id.
     * @return int
     */
    public static function getMaximumImagesSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects_images AS i
             WHERE i.project_id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an file
     *
     * @param int $id The project id.
     * @return int
     */
    public static function getMaximumFilesSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects_files AS i
             WHERE i.project_id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an videos
     *
     * @param int $id The project id.
     * @return int
     */
    public static function getMaximumVideosSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
             FROM projects_videos AS i
             WHERE i.project_id = ?',
            array((int)$id));
    }

    /**
     * Retrieve the unique URL for an item
     *
     * @param string $url
     * @param int [optional] $id    The id of the item to ignore.
     * @return string
     */
    public static function getURL($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string)$url);
        $db = BackendModel::getContainer()->get('database');

        // new item
        if ($id === null) {
            // already exists
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURL($url);
            }
        } // current category should be excluded
        else {
            // already exists
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ? AND i.id != ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url, $id))
            ) {
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
     * @param int [optional] $id The id of the category to ignore.
     * @return string
     */
    public static function getURLForCategory($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string)$url);
        $db = BackendModel::getContainer()->get('database');

        // new category
        if ($id === null) {
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects_categories AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForCategory($url);
            }
        } // current category should be excluded
        else {
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects_categories AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ? AND i.id != ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url, $id))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForCategory($url, $id);
            }
        }

        return $url;
    }

    /**
     * Retrieve the unique URL for a client
     *
     * @param string $url
     * @param int [optional] $id The id of the client to ignore.
     * @return string
     */
    public static function getURLForClient($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string)$url);
        $db = BackendModel::getContainer()->get('database');

        // new client
        if ($id === null) {
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects_clients AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForClient($url);
            }
        } // current client should be excluded
        else {
            if ((bool)$db->getVar(
                'SELECT 1
                 FROM projects_clients AS i
                 INNER JOIN meta AS m ON i.meta_id = m.id
                 WHERE i.language = ? AND m.url = ? AND i.id != ?
                 LIMIT 1',
                array(BL::getWorkingLanguage(), $url, $id))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForClient($url, $id);
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
     * @param array [optional] $meta The metadata for the category to insert.
     * @return int
     */
    public static function insertCategory(array $item, $meta = null)
    {
        $db = BackendModel::getContainer()->get('database');

        // insert meta
        if ($meta !== null) $item['meta_id'] = $db->insert('meta', $meta);

        // build extra
        $extra = array(
            'module' => 'projects',
            'type' => 'widget',
            'label' => 'Category',
            'action' => 'category',
            'data' => null,
            'hidden' => 'N',
            'sequence' => $db->getVar(
                    'SELECT MAX(i.sequence) + 1
                     FROM modules_extras AS i
                     WHERE i.module = ?',
                    array('content_blocks')
                )
        );

        if (is_null($extra['sequence'])) $extra['sequence'] = $db->getVar(
            'SELECT CEILING(MAX(i.sequence) / 1000) * 1000
             FROM modules_extras AS i'
        );

        // insert extra
        $item['extra_id'] = $db->insert('modules_extras', $extra);
        $extra['id'] = $item['extra_id'];

        // insert and return the new revision id
        $item['id'] = $db->insert('projects_categories', $item);

        // update extra (item id is now known)
        $extra['data'] = serialize(array(
                'id' => $item['id'],
                'extra_label' => $item['title'],
                'language' => $item['language'],
                'edit_url' => BackendModel::createURLForAction('edit_category', 'projects', $item['language']) . '&id=' . $item['id'])
        );

        $db->update(
            'modules_extras',
            $extra,
            'id = ? AND module = ? AND type = ? AND action = ?',
            array($extra['id'], $extra['module'], $extra['type'], $extra['action'])
        );

        BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());

        return $item['id'];
    }

    /**
     * Insert a client in the database
     *
     * @param array $item
     * @param array [optional] $meta The metadata for the category to insert.
     * @return int
     */
    public static function insertClient(array $item, $meta = null)
    {
        $db = BackendModel::getContainer()->get('database');

        if ($meta !== null) $item['meta_id'] = $db->insert('meta', $meta);
        $item['id'] = $db->insert('projects_clients', $item);

        BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());

        return $item['id'];
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertImage($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('projects_images', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertFile($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('projects_files', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertVideo($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('projects_videos', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertRelatedProject($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('projects_related', $item);
    }

    /**
     * Update a certain question
     *
     * @param array $item
     */
    public static function update(array $item)
    {
        BackendModel::getContainer()->get('database')->update('projects', $item, 'id = ?', array((int)$item['id']));
        BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
    }

    /**
     * Update a certain category
     *
     * @param array $item
     */
    public static function updateCategory(array $item)
    {
        $db = BackendModel::getContainer()->get('database');

        // build extra
        $extra = array(
            'id' => $item['extra_id'],
            'module' => 'projects',
            'type' => 'widget',
            'label' => 'Category',
            'action' => 'category',
            'data' => serialize(array(
                    'id' => $item['id'],
                    'extra_label' => $item['title'],
                    'language' => $item['language'],
                    'edit_url' => BackendModel::createURLForAction('edit') . '&id=' . $item['id'])
            ),
            'hidden' => 'N');

        // update extra
        $db->update('modules_extras', $extra, 'id = ? AND module = ? AND type = ? AND action = ?', array($extra['id'], $extra['module'], $extra['type'], $extra['action']));

        // update category
        $db->update('projects_categories', $item, 'id = ?', array($item['id']));

        BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
    }

    /**
     * Update a certain client
     *
     * @param array $item
     */
    public static function updateClient(array $item)
    {
        BackendModel::getContainer()->get('database')->update('projects_clients', $item, 'id = ?', array($item['id']));
        BackendModel::invalidateFrontendCache('projects', BL::getWorkingLanguage());
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateImage(array $item)
    {
        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)BackendModel::getContainer()->get('database')->update(
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
        if (isset($item['id']) && self::existsImage($item['id'])) {
            self::updateImage($item);
        } else {
            $item['id'] = self::insertImage($item);
        }

        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)$item['id'];
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateFile(array $item)
    {
        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)BackendModel::getContainer()->get('database')->update(
            'projects_files',
            $item,
            'id = ?',
            array($item['id'])
        );
    }

    /**
     * @param array $item
     * @return int
     */
    public static function saveFile(array $item)
    {
        if (isset($item['id']) && self::existsFile($item['id'])) {
            self::updateFile($item);
        } else {
            $item['id'] = self::insertFile($item);
        }

        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)$item['id'];
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateVideo(array $item)
    {
        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)BackendModel::getContainer()->get('database')->update(
            'projects_videos',
            $item,
            'id = ?',
            array($item['id'])
        );
    }

    /**
     * @param array $item
     * @return int
     */
    public static function saveVideo(array $item)
    {
        if (isset($item['id']) && self::existsVideo($item['id'])) {
            self::updateVideo($item);
        } else {
            $item['id'] = self::insertVideo($item);
        }

        BackendModel::invalidateFrontendCache('projectsCache');
        return (int)$item['id'];
    }

    /**
     *
     * @param int $projectId The id of the item where to assign the related projects.
     * @param array $relatedProjects The related projects for the item.
     * @param array [optional] $oRelatedProjects The related projects already existing for the item. If not provided a new record will be created.
     *
     * @return int
     */
    public static function saveRelatedProjects($projectId, $relatedProjects, $oRelatedProjects = null)
    {
        $item['project_id'] = $projectId;

        if (isset($oRelatedProjects)) {
            // Insert new records
            $newRelatedProjects = array_diff($relatedProjects, $oRelatedProjects);
            foreach ($newRelatedProjects AS $key => $newRelatedProject) {
                $item['related_project_id'] = $newRelatedProject;
                self::insertRelatedProject($item);
            }

            // Delete old records
            $oldRelatedProjects = array_diff($oRelatedProjects, $relatedProjects);
            foreach ($oldRelatedProjects AS $key => $oldRelatedProject) {
                $item['related_project_id'] = $oldRelatedProject;
                self::deleteRelatedProject($item['project_id'], $item['related_project_id']);
            }
        } else {
            // Insert new records
            foreach ($relatedProjects AS $key => $relatedProject) {
                $item['related_project_id'] = $relatedProject;
                self::insertRelatedProject($item);
            }
        }
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateRelatedProject(array $item)
    {
        return (int)BackendModel::getContainer()->get('database')->update(
            'projects_related',
            $item,
            array()
        );
    }
}
