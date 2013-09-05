{*
	variables that are available:
	- {$projectsCategories}: contains all categories, along with all questions inside a category
*}


{option:projectsCategories}
	<section id="projectsIndex" class="mod">
		<div class="inner">
			<div class="bd">
				{iteration:projectsCategories}
					<section class="mod">
						<div class="inner">
                        	{option:allowMultipleCategories}
							<header class="hd">
								<h2 id="{$projectsCategories.url}"><a href="{$projectsCategories.full_url}" title="{$projectsCategories.title}">{$projectsCategories.title}</a></h2>
							</header>
                            {/option:allowMultipleCategories}                            
							<div class="bd content">
							{iteration:projectsCategories.projects}
								{option:projectsCategories.projects}
									<article class="projectBlock">
										<h3>
											<a href="{$projectsCategories.projects.full_url}" title="{$projectsCategories.projects.title}">
												<span>{$projectsCategories.projects.title}</span>
												<img src="{$projectsCategories.projects.image}" title="{$projectsCategories.projects.title}" alt="{$projectsCategories.projects.title}" />
											</a>
										</h3>
										{$projectsCategories.projects.introduction}
									</article>
								{/option:projectsCategories.projects}
							{/iteration:projectsCategories.projects}
							</div>
						</div>
					</section>
				{/iteration:projectsCategories}
			</div>
		</div>
	</section>
{/option:projectsCategories}