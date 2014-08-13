<?php

namespace Backend\Modules\Projects\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
 
use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the projects module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@reclame-mediabureau.nl>
 */
class Installer extends ModuleInstaller
{
	/**
	 * @var	int
	 */
	private $defaultCategoryId, $defaultClientId;

	/**
	 * Add a category for a language
	 *
	 * @param string $language
	 * @param string $title
	 * @param string $url
	 * @return int
	 */
	private function addCategory($language, $title, $url)
	{
		// build array
		$item['meta_id'] = $this->insertMeta($title, $title, $title, $url);
		$item['language'] = (string) $language;
		$item['title'] = (string) $title;
		$item['sequence'] = 1;

		return (int) $this->getDB()->insert('projects_categories', $item);
	}

	/**
	 * Fetch the id of the first category in this language we come across
	 *
	 * @param string $language
	 * @return int
	 */
	private function getCategory($language)
	{
		return (int) $this->getDB()->getVar(
			'SELECT id
			 FROM projects_categories
			 WHERE language = ?',
			array((string) $language));
	}
	
	/**
	 * Add a client for a language
	 *
	 * @param string $language
	 * @param string $title
	 * @param string $url
	 * @return int
	 */
	private function addClient($language, $title, $url)
	{
		// build array
		$item['meta_id'] = $this->insertMeta($title, $title, $title, $url);
		$item['language'] = (string) $language;
		$item['title'] = (string) $title;
		$item['sequence'] = 1;

		return (int) $this->getDB()->insert('projects_clients', $item);
	}
			
	/**
	 * Fetch the id of the first client in this language we come across
	 *
	 * @param string $language
	 * @return int
	 */
	private function getClient($language)
	{
		return (int) $this->getDB()->getVar(
			'SELECT id
			 FROM projects_clients
			 WHERE language = ?',
			array((string) $language));
	}
	
	/**
	 * Install the module
	 */
	public function install()
	{
		$this->importSQL(dirname(__FILE__) . '/Data/install.sql');

		$this->addModule('Projects');

		$this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

		$this->makeSearchable('Projects');
		$this->setModuleRights(1, 'Projects');
		
		// projects and index
		$this->setActionRights(1, 'Projects', 'Index');
		$this->setActionRights(1, 'Projects', 'Add');
		$this->setActionRights(1, 'Projects', 'Edit');
		$this->setActionRights(1, 'Projects', 'Delete');
		$this->setActionRights(1, 'Projects', 'SequenceProjects');
		
		// categories
		$this->setActionRights(1, 'Projects', 'Categories');
		$this->setActionRights(1, 'Projects', 'AddCategory');
		$this->setActionRights(1, 'Projects', 'EditCategory');
		$this->setActionRights(1, 'Projects', 'DeleteCategory');
		$this->setActionRights(1, 'Projects', 'Sequence');
		
		// clients
		$this->setActionRights(1, 'Projects', 'Clients');
		$this->setActionRights(1, 'Projects', 'AddClient');
		$this->setActionRights(1, 'Projects', 'EditClient');
		$this->setActionRights(1, 'Projects', 'DeleteClient');
		$this->setActionRights(1, 'Projects', 'SequenceClients');
				
		// media
		$this->setActionRights(1, 'Projects', 'Media');
		$this->setActionRights(1, 'Projects', 'AddImage');
		$this->setActionRights(1, 'Projects', 'EditImage');
		$this->setActionRights(1, 'Projects', 'SequenceImages');
		
		$this->setActionRights(1, 'Projects', 'AddFile');
		$this->setActionRights(1, 'Projects', 'EditFile');
		$this->setActionRights(1, 'Projects', 'SequenceFiles');
		
		$this->setActionRights(1, 'Projects', 'AddVideo');
		$this->setActionRights(1, 'Projects', 'EditVideo');
		$this->setActionRights(1, 'Projects', 'SequenceVideos');
		
		$this->setActionRights(1, 'Projects', 'MassAction');
		
		// blocks or widgets
		$projectsId = $this->insertExtra('Projects', 'block', 'Projects');
		$this->insertExtra('Projects', 'widget', 'Spotlight', 'spotlight');
		$this->insertExtra('Projects', 'widget', 'Categories', 'categories');
		$this->insertExtra('Projects', 'widget', 'Clients', 'clients');
		$this->setActionRights(1, 'Projects', 'Settings');
				
		// settings		
		$this->setSetting('Projects', 'width1', (int)400);
		$this->setSetting('Projects', 'height1', (int)300);
		$this->setSetting('Projects', 'allow_enlargment1', true);
		$this->setSetting('Projects', 'force_aspect_ratio1', true);
		
		$this->setSetting('Projects', 'width2', (int)800);
		$this->setSetting('Projects', 'height2', (int)600);
		$this->setSetting('Projects', 'allow_enlargment2', true);
		$this->setSetting('Projects', 'force_aspect_ratio2', true);
		
		$this->setSetting('Projects', 'width3', (int)1600);
		$this->setSetting('Projects', 'height3', (int)1200);
		$this->setSetting('Projects', 'allow_enlargment3', true);
		$this->setSetting('Projects', 'force_aspect_ratio3', true);
		
		$this->setSetting('Projects', 'allow_multiple_categories', true);
				
		foreach($this->getLanguages() as $language)
		{
			$this->defaultCategoryId = $this->getCategory($language);

			// no category exists
			if($this->defaultCategoryId == 0)
			{
				$this->defaultCategoryId = $this->addCategory($language, 'Default', 'default');
			}

			$this->defaultClientId = $this->getClient($language);
			
			// no client exists
			if($this->defaultClientId == 0)
			{
				$this->defaultClientId = $this->addClient($language, 'Default', 'default');
			}

			// check if a page for the projects already exists in this language
			if(!(bool) $this->getDB()->getVar(
				'SELECT 1
				 FROM pages AS p
				 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
				 WHERE b.extra_id = ? AND p.language = ?
				 LIMIT 1',
				 array($projectsId, $language)))
			{
				// insert page
				$this->insertPage(array('title' => 'Projects',
										'language' => $language),
										null,
										array('extra_id' => $projectsId));
			}
		

			$this->installExampleData($language);
		}

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationprojectsId = $this->setNavigation($navigationModulesId, 'Projects');
		$this->setNavigation($navigationprojectsId, 'Projects', 'projects/index', array('projects/add', 'projects/edit', 'projects/media', 'projects/add_image', 'projects/edit_image', 'projects/add_file', 'projects/edit_file', 'projects/add_video', 'projects/edit_video'));
		$this->setNavigation($navigationprojectsId, 'Categories', 'projects/categories', array('projects/add_category', 'projects/edit_category'));
		$this->setNavigation($navigationprojectsId, 'Clients', 'projects/clients', array('projects/add_client', 'projects/edit_client'));
		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$this->setNavigation($navigationModulesId, 'Projects', 'projects/settings');
	}
	

	/**
	 * Install example data
	 *
	 * @param string $language The language to use.
	 */
	private function installExampleData($language)
	{
		// get db instance
		$db = $this->getDB();

		// check if blogposts already exist in this language
		if(!(bool) $db->getVar(
			'SELECT 1
			 FROM projects
			 WHERE language = ?
			 LIMIT 1',
			array($language)))
		{
			
			// insert sample project
			$projectId = $db->insert( 'projects', array(
									'category_id' => $this->defaultCategoryId,
									'client_id' => $this->defaultClientId,
									'user_id' => $this->getDefaultUserID(),
									'meta_id' => $this->insertMeta('James Bond', 'James Bond', 'James Bond', 'james-bond'),
									'language' => $language,
									'title' => 'James Bond',
									'introduction' => '<p>James Bond is created by Ian Fleming.</p>',
									'text' =>  '<p>James Bond, code name 007, is a fictional character created in 1953 by writer Ian Fleming, who featured him in twelve novels and two short-story collections. Six other authors have written authorised Bond novels or novelizations since Flemings death in 1964: Kingsley Amis, Christopher Wood, John Gardner, Raymond Benson, Sebastian Faulks, and Jeffery Deaver; a new novel, written by William Boyd, is planned for release in 2013.[1] Additionally, Charlie Higson wrote a series on a young James Bond, and Kate Westbrook wrote three novels based on the diaries of a recurring series character, Moneypenny.</p>
												<p>The fictional British Secret Service agent has also been adapted for television, radio, comic strip, and video game formats in addition to having been used in the longest continually running and the second-highest grossing film series to date, which started in 1962 with Dr. No, starring Sean Connery as Bond. As of 2013, there have been twenty-three films in the Eon Productions series. The most recent Bond film, Skyfall (2012), stars Daniel Craig in his third portrayal of Bond; he is the sixth actor to play Bond in the Eon series. There have also been two independent productions of Bond films: Casino Royale (a 1967 spoof) and Never Say Never Again (a 1983 remake of an earlier Eon-produced film, Thunderball).</p>
												<p>The Bond films are renowned for a number of features, including the musical accompaniment, with the theme songs having received Academy Award nominations on several occasions, and one win. Other important elements which run through most of the films include Bonds cars, his guns, and the gadgets with which he is supplied by Q Branch.</p>
												<p><a href="http://en.wikipedia.org/wiki/James_Bond">From Wikipedia, the free encyclopedia</a></p>',
									'created_on' => gmdate('Y-m-d H:i:00'),
									'date' => gmdate('Y-m-d H:i:00'),
									'hidden' => 'N',
									'spotlight' => 'Y',
									'sequence' => 1				
			));
				
			// insert sample image 1
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'Sean Connery',
						'filename' => '1378315731.png',
						'sequence' => 1				
			));
						
			// insert sample image 2
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'George Lazenby',
						'filename' => '1378315749.png',
						'sequence' => 2
			));
						
			// insert sample image 3
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'Roger Moore',
						'filename' => '1378315777.png',
						'sequence' => 3
			));
						
			// insert sample image 4
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'Timothy Dalton',
						'filename' => '1378315795.png',
						'sequence' => 4
			));
						
			// insert sample image 5
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'Pierce Brosnan',
						'filename' => '1378315808.png',
						'sequence' => 5
			));
						
			// insert sample image 6
			$db->insert('projects_images', array(
						'project_id' => $projectId,
						'title' => 'Daniel Craig',
						'filename' => '1378315820.png',
						'sequence' => 6
			));
			
			$fs = new Filesystem();
			if(!$fs->exists(PATH_WWW . '/src/Frontend/Files/Projects/')) $fs->mkdir(PATH_WWW . '/src/Frontend/Files/Projects/');
			$fs->mirror(PATH_WWW . '/src/Backend/Modules/Projects/Installer/Data/Images/', PATH_WWW . '/src/Frontend/Files/Projects/' . $projectId);		
		}
	}
}
