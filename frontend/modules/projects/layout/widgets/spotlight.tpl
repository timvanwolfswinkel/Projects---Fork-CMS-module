{*
	variables that are available:
	- {$widgetProjectsSpotlight}: contains an array with a spotlight project
*}

{option:widgetProjectsSpotlight}
	<section id="projectSpotlight" class="mod">
		<div class="inner">
			<header class="hd">
				<h3>{$lblSpotlight|ucfirst}</h3>
			</header>
			<div class="bd content">
				<a title="{$widgetProjectsSpotlight.title}" href="{$widgetProjectsSpotlight.full_url}">
					<h4>{$widgetProjectsSpotlight.title}</h4>
					<img alt="{$widgetProjectsSpotlight.title}" src="{$widgetProjectsSpotlight.image}">
				</a>
				<p>{$widgetProjectsSpotlight.introduction}</p>
			</div>
		</div>
	</section>
{/option:widgetProjectsSpotlight}