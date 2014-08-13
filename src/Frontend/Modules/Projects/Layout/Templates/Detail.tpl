<div class="projects projects-detail">
	<article>
		<div class="hd">
			<h1 itemprop="name">{$item.title}</h1>
			<p class="date">
				<time datetime="{$item.date|date:'c':{$LANGUAGE}}">
				<small>{$item.created_on|timeago}</small>
				{$item.date|date:'d':{$LANGUAGE}} {$item.date|date:'F':{$LANGUAGE}} {$item.date|date:'Y':{$LANGUAGE}}
				</time>
			</p>
			<p class="client">
				{$lblClient|ucfirst}
				<a href="{$item.client_full_url}" title="{$item.client_title}"><b>{$item.client_title}</b></a>
			</p>
			<p class="category">
				{$lblCategory|ucfirst}
				<a href="{$item.category_full_url}" title="{$item.category_title}"><b>{$item.category_title}</b></a>
			</p>
		</div>
		<div class="bd">
			<p>{$item.text}</p>
			{option:images}
			<div class="projectImages">
				<h3>{$lblImages|ucfirst}</h3>
				{iteration:images}
					<a class="colorbox" rel="group1" href="{$images.sizes.large}" title="{$images.title}">
						<img src="{$images.sizes.small}" alt="{$images.title}" title="{$images.title}" />
					</a>
				{/iteration:images}
				{iteration:videos}
					<a class="fancybox fancybox.iframe" rel="gallery" href="{$videos.url}">
						<img src="{$videos.image}" alt="{$videos.title}" title="{$videos.title}">
					</a>
				{/iteration:videos}
			</div>
			{/option:images}
			<div class="divider"></div>
			{option:related}
                <div class="relatedProjects">
                    <h3>{$lblRelatedProjects|ucfirst}</h3>
                    {iteration:related}
                        <div class="relatedProject">
                            <small><a href="{$related.url}">{$related.title}</a></small>
                            <a href="{$related.url}">
                                <img src="{$related.image}" alt="{$related.title}" title="{$related.title}" />
                            </a>
                        </div>
                    {/iteration:related}
                </div>
			{/option:related}
		</div>
	</article>
	<div>
		<a href="{$var|geturlforblock:'Projects'}" title="{$msgToProjectsOverview|ucfirst}">{$msgToProjectsOverview|ucfirst}</a>
	</div>
</div>
