{*
	variables that are available:
	- {$categories}: contains the categories which have projects in it
	- {$clients}: contains all clients
	- {$projects}: contains all projects
*}

<div class="projects projects-index">
    <div class="category">
    <h2>{$lblCategory|ucfirst}: {$category.title}</h2>
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
    </div>
</div>
