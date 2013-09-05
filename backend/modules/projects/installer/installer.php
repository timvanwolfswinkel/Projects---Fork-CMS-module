<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the projects module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 */
class ProjectsInstaller extends ModuleInstaller
{
	/**
	 * @var	int
	 */
	private $defaultCategoryId;

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
	 * Install the module
	 */
	public function install()
	{
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		$this->addModule('projects');

		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		$this->makeSearchable('projects');
		$this->setModuleRights(1, 'projects');
		
		// projects and index
		$this->setActionRights(1, 'projects', 'index');
		$this->setActionRights(1, 'projects', 'add');
		$this->setActionRights(1, 'projects', 'edit');
		$this->setActionRights(1, 'projects', 'delete');
		$this->setActionRights(1, 'projects', 'sequence_projects');
		
		// categories
		$this->setActionRights(1, 'projects', 'categories');
		$this->setActionRights(1, 'projects', 'add_category');
		$this->setActionRights(1, 'projects', 'edit_category');
		$this->setActionRights(1, 'projects', 'delete_category');
		$this->setActionRights(1, 'projects', 'sequence');
		
		//images
		$this->setActionRights(1, 'projects', 'images');
		$this->setActionRights(1, 'projects', 'add_image');
		$this->setActionRights(1, 'projects', 'edit_image');
		$this->setActionRights(1, 'projects', 'mass_action');
		$this->setActionRights(1, 'projects', 'sequence_images');
		
		// blocks or widgets
		$projectsId = $this->insertExtra('projects', 'block', 'Projects');
		$this->insertExtra('projects', 'widget', 'Spotlight', 'spotlight');
		$this->insertExtra('projects', 'widget', 'Categories', 'categories');
		$this->setActionRights(1, 'projects', 'settings');
				
		// settings		
		$this->setSetting('projects', 'width1', (int)400);
		$this->setSetting('projects', 'height1', (int)300);
		$this->setSetting('projects', 'allow_enlargment1', true);
		$this->setSetting('projects', 'force_aspect_ratio1', true);
		
		$this->setSetting('projects', 'width2', (int)800);
		$this->setSetting('projects', 'height2', (int)600);
		$this->setSetting('projects', 'allow_enlargment2', true);
		$this->setSetting('projects', 'force_aspect_ratio2', true);
		
		$this->setSetting('projects', 'width3', (int)1600);
		$this->setSetting('projects', 'height3', (int)1200);
		$this->setSetting('projects', 'allow_enlargment3', true);
		$this->setSetting('projects', 'force_aspect_ratio3', true);
		
		$this->setSetting('projects', 'allow_multiple_categories', true);
				
		foreach($this->getLanguages() as $language)
		{
			$this->defaultCategoryId = $this->getCategory($language);

			// no category exists
			if($this->defaultCategoryId == 0)
			{
				$this->defaultCategoryId = $this->addCategory($language, 'Default', 'default');
			}

			// check if a page for the faq already exists in this language
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
		$this->setNavigation($navigationprojectsId, 'Projects', 'projects/index', array('projects/add', 'projects/edit', 'projects/images', 'projects/edit_image', 'projects/add_image'));
		$this->setNavigation($navigationprojectsId, 'Categories', 'projects/categories', array('projects/add_category', 'projects/edit_category'));
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
			
			// copy images into files path
			SpoonDirectory::copy(PATH_WWW . '/backend/modules/projects/installer/data/images', PATH_WWW . '/frontend/files/projects/' . $projectId);
		
		}
	}
}
