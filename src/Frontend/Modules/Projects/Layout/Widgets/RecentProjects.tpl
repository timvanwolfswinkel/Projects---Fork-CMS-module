{option:widgetProjectsRecent}
    <section class="projects">
        {iteration:widgetProjectsRecent}
            <article class="full article">
                <div class="centered articleContent plain">
                    <header class="hd">
                        <h2><a title="{$widgetProjectsRecent.title}" href="{$widgetProjectsRecent.full_url}">{$widgetProjectsRecent.title}</a></h2>
                    </header>
                    {$widgetProjectsRecent.introduction}
                    <div class="bd content">

                    </div>
                </div>
                <a class="readmore" title="{$widgetProjectsRecent.title}" href="{$widgetProjectsRecent.full_url}">
                    {$lblMore|ucfirst}
                </a>
            </article>
        {/iteration:widgetProjectsRecent}
    </section>
{/option:widgetProjectsRecent}
