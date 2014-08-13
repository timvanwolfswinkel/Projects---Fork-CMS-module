{*
	variables that are available:
	- {$widgetProjectsClients}: contains an array with the project categories
*}

{option:widgetProjectsClients}
<section id="projectsClientsWidget" class="mod">
	<div class="inner">
		<header class="hd">
			<h3>{$lblClients|ucfirst}</h3>
		</header>
		<div class="bd content">
			<ul>
				{iteration:widgetProjectsClients}
					<li><a href="{$widgetProjectsClients.full_url}">{$widgetProjectsClients.title}</a></li>
				{/iteration:widgetProjectsClients}
			</ul>
		</div>
	</div>
</section>
{/option:widgetProjectsClients}

