{*
	variables that are available:
	- {$record}: contains the category
	- {$projects}: contains all projects
*}


<section id="projectsCategory" class="mod">
	<div class="inner">
		<header class="hd">
			<h1>{$record.title}</h1>
		</header>
		<div class="bd">
			{option:projects}
				{iteration:projects}
					<article class="projectBlock">
						<h2>
							<a href="{$projects.full_url}" title="{$projects.title}">
								<span>{$projects.title}</span>
								<img src="{$projects.image}" title="{$projects.title}" alt="{$projects.title}" />
							</a>
						</h2>
						{$projects.introduction}
					</article>
				{/iteration:projects}
			{/option:projects}

			{option:!projects}
				<p>{$msgNoProjectsInCategory|ucfirst}</p>
			{/option:!projects}

			<p class="projectsBack"><a href="{$var|geturlforblock:'projects'}" title="{$msgToProjectsOverview|ucfirst}">{$msgToProjectsOverview|ucfirst}</a></p>
		</div>
	</div>
</section>