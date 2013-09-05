{*
	variables that are available:
	- {$item}: contains data about the question
	- {$related}: the related items
*}
    
<div id="faqDetail">
	<article class="mod article">
		<div class="inner">
			<header class="hd">
				<h1>{$item.title}</h1>
                {option:settings.allow_multiple_categories}
				<ul>
					<li>
						{* Category*}
						{$lblIn|ucfirst} {$lblThe} {$lblCategory} <a href="{$item.category_full_url}" title="{$item.category_title}">{$item.category_title}</a>
                        {option:!item.tags}.{/option:!item.tags}

						{* Tags*}
						{option:item.tags}
							{$lblWith} {$lblThe} {$lblTags}
							{iteration:item.tags}
								<a href="{$item.tags.full_url}" rel="tag" title="{$item.tags.name}">{$item.tags.name}</a>{option:!item.tags.last}, {/option:!item.tags.last}{option:item.tags.last}.{/option:item.tags.last}
							{/iteration:item.tags}
						{/option:item.tags}
					</li>
				</ul>
                {/option:settings.allow_multiple_categories}
                
				{* Tags *}
                {option:!settings.allow_multiple_categories}
                    {option:item.tags}
                    <ul>
                        <li>
                            {$lblWith} {$lblThe} {$lblTags}
                            {iteration:item.tags}
                                <a href="{$item.tags.full_url}" rel="tag" title="{$item.tags.name}">{$item.tags.name}</a>{option:!item.tags.last}, {/option:!item.tags.last}{option:item.tags.last}.{/option:item.tags.last}
                            {/iteration:item.tags}
                        </li>
                    </ul>
                    {/option:item.tags}
                {/option:!settings.allow_multiple_categories}
			</header>
			<div class="bd content">
				{$item.text}
				{option:images}  
	            	{iteration:images}
	            		<a class="fancybox" rel="gallery" href="{$images.image_big}">
	            			<img src="{$images.image_thumb}" alt="{$images.title}" title="{$images.title}" />
	            		</a>
	            	{/iteration:images}
				{/option:images} 
				
				<p class="projectsBack"><a href="{$var|geturlforblock:'projects'}" title="{$msgToProjectsOverview|ucfirst}">{$msgToProjectsOverview|ucfirst}</a></p>
				
			</div>
		</div>
	</article>
</div>