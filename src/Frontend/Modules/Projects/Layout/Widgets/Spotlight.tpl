{*
	variables that are available:
	- {$widgetProjectsSpotlight}: contains an array with a spotlight project
*}

{option:widgetProjectsSpotlight}
<section class="projects">
	<article class="full article">
		<div class="centered articleContent plain">
			<header class="hd">
				<h3><a href="{$LANGUAGE}/{$lblAboutWeMetalLink|lowercase}/{$lblProjects|lowercase}" title="">{$lblProjects|ucfirst}</a></h3>
				<h4><a title="{$widgetProjectsSpotlight.title}" href="{$widgetProjectsSpotlight.full_url}">{$widgetProjectsSpotlight.title}</a></h4>
			</header>
				{$widgetProjectsSpotlight.introduction}
			<div class="bd content">
			
			</div>
		</div>
		<a class="readmore" title="{$widgetProjectsSpotlight.title}" href="{$widgetProjectsSpotlight.full_url}">
			{$lblMoreProjects}
		</a>
	</article>
</section>
{/option:widgetProjectsSpotlight}
