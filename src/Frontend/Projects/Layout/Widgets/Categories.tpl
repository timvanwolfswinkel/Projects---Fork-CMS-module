{*
	variables that are available:
	- {$widgetProjectsCategories}: contains an array with the project categories
*}

{option:widgetProjectsCategories}
<section id="projectsCategoriesWidget" class="mod">
	<div class="inner">
		<header class="hd">
			<h3>{$lblCategories|ucfirst}</h3>
		</header>
		<div class="bd content">
			<ul>
				{iteration:widgetProjectsCategories}
					<li><a href="{$widgetProjectsCategories.full_url}">{$widgetProjectsCategories.title}</a></li>
				{/iteration:widgetProjectsCategories}
			</ul>
		</div>
	</div>
</section>
{/option:widgetProjectsCategories}

