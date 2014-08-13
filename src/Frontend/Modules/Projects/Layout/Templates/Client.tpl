{*
	variables that are available:
	- {$client}: contains the client
	- {$projects}: contains all projects
*}

<div class="projects projects-client">
    {option:client}
        <h2>{$lblClient|ucfirst}: {$client.title}</h2>
	
	{option:client.image}
	    <img src="{$client.image}" alt="{$client.title}"/>
	{/option:client.image}
       
        {option:projects}
            <div class="project-list">
                {iteration:projects}
                    <article>
                            <div class="inner">
                                <header>
                                    <h1 class="h2"><a href="{$projects.full_url}" title="{$projects.title}">{$projects.title|ucfirst}</a></h1>
                                    {option:projects.images}
					{iteration:projects.images}
					    <img src="{$projects.images.sizes.small}" title="{$projects.title}" alt="{$projects.title}"/>
					{/iteration:projects.images}
				    {/option:projects.images}
                                    <p class="date">
                                        <time datetime="{$projects.date|date:'d':{$LANGUAGE}}{$projects.date|date:'F':{$LANGUAGE}}{$projects.date|date:'Y':{$LANGUAGE}}">{$projects.date|date:'d':{$LANGUAGE}} {$projects.date|date:'F':{$LANGUAGE}} {$projects.date|date:'Y':{$LANGUAGE}}</time>
                                    </p>
                                    {$projects.introduction}
                                </header>
                            </div>
                    </article>
                {/iteration:projects}
                <div class="ft">
                    <p>
                        <a href="{$var|geturlforblock:'Projects'}"title="{$msgToProjectsOverview|ucfirst}">{$msgToProjectsOverview|ucfirst}</a>
                    </p>
                </div>
            </div>
        {/option:projects}

    {/option:client}
</div>
