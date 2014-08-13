{*
	variables that are available:
	- {$categories}: contains the categories which have projects in it
	- {$clients}: contains all clients
	- {$projects}: contains all projects
*}

<div class="projects projects-index">
    {option:!categories}
        <p>{$msgNoCategories|ucfirst}</p>
    {/option:!categories}
    
    {option:categories}
    <div class="categories">
	<h2>{$lblCategories|ucfirst}:</h2>
	<ul>
	    {iteration:categories}
	    <li><a href="{$categories.full_url}" title="{$categories.title}">{$categories.title|ucfirst}</a></li>
	    {/iteration:categories}
	</ul>
    </div>
    {/option:categories}

    {option:projects}
        <div class="projects">
            {iteration:projects}
                <article>
                    <a href="{$projects.full_url}" title="{$projects.title}">
                        <div class="inner">
                            <header>
                                <h2 class="h2">{$projects.title|ucfirst}</h2>
                                <p class="date"><time datetime="{$projects.date|date:'d':{$LANGUAGE}}{$projects.date|date:'F':{$LANGUAGE}}{$projects.date|date:'Y':{$LANGUAGE}}">{$projects.date|date:'d':{$LANGUAGE}} {$projects.date|date:'F':{$LANGUAGE}} {$projects.date|date:'Y':{$LANGUAGE}}</time></p>
                                {$projects.introduction}
                            </header>
                        </div>
                        {option:projects.images}
                            <div class="images clearfix">
                                {iteration:projects.images}
                                    <img src="{$projects.images.sizes.small}" alt="{$projects.images.title}" title="{$projects.images.title}" />
                                {/iteration:projects.images}
                            </div>
                        {/option:projects.images}
                    </a>
                </article>
            {/iteration:projects}
        </div>
        {include:Core/Layout/Templates/Pagination.tpl}
    {/option:projects}

    {option:clients}
        <div class="clients">
	    <h2>{$lblClients|ucfirst}:</h2>
	    <ul>
		{iteration:clients}
		<li>
		    <a class=title" href="{$clients.full_url}">{$clients.title}</a>
		    {option:clients.image}
                    <a class="image" href="{$clients.full_url}">
                        <img src="{$clients.image}" title="{$clients.title}" alt="{$clients.title}" class="clientImage"/>
                    </a>
		    {/option:clients.image}
		</li>
		{/iteration:clients}
	    </ul>
        </div>
    {/option:clients}
</div>