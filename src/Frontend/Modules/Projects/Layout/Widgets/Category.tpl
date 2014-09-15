<!--{$widgetProjectsInCategory|dump}-->

{option:widgetProjectsInCategory}
<div class="projectsContainer">
	{iteration:widgetProjectsInCategory}
		<article class="block summary {$widgetProjectsInCategory.category_url}">
			<a href="{$widgetProjectsInCategory.full_url}" title="{$widgetProjectsInCategory.title}" class="summaryContent">
				<div class="inner">
					<header class="summaryContentText autoEllipsis">
						<h1 class="h2">{$widgetProjectsInCategory.title|ucfirst}</h1>
						<img src="{$widgetProjectsInCategory.image}" title="{$widgetProjectsInCategory.title}" alt="{$widgetProjectsInCategory.title}" class="projectImage"/>
						<p class="date"><time datetime="{$widgetProjectsInCategory.date|date:'d':{$LANGUAGE}}{$widgetProjectsInCategory.date|date:'F':{$LANGUAGE}}{$widgetProjectsInCategory.date|date:'Y':{$LANGUAGE}}">{$widgetProjectsInCategory.date|date:'d':{$LANGUAGE}} {$widgetProjectsInCategory.date|date:'F':{$LANGUAGE}} {$widgetProjectsInCategory.date|date:'Y':{$LANGUAGE}}</time></p>
						{$widgetProjectsInCategory.introduction}
					</header>
				</div>
			</a>
			<footer class="summaryFooter">
				<a href="{$widgetProjectsInCategory.category_full_url}" class="textlink category">{$widgetProjectsInCategory.category_title}</a>
			</footer>
		</article>
	{/iteration:widgetProjectsInCategory}
</div>
{include:core/layout/templates/pagination.tpl}
{/option:widgetProjectsInCategory}

{option:!widgetProjectsInCategory}
	<p>{$lblNoProjectsInCategory}</p>
{/option:!widgetProjectsInCategory}
