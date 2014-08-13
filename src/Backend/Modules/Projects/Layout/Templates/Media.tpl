{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$project.title}: {$lblMedia}</h2>
</div>

<div class="tabs">
		<ul>
			<li><a href="#tabImages">{$lblImages|ucfirst}</a></li>
			<li><a href="#tabFiles">{$lblFiles|ucfirst}</a></li>
			<li><a href="#tabVideos">{$lblVideos|ucfirst}</a></li>
		</ul>
		
		<div id="tabImages">
     {option:showProjectsAddImage}
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_image'}&amp;project_id={$project.id}" class="button icon iconAdd" title="{$lblAddImage|ucfirst}">
					<span>{$lblAddImage|ucfirst}</span>
				</a>
			</div>
			{/option:showProjectsAddImage}
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProjectImagesHolder">
        {option:dataGridImages}
          <div class="dataGridImagesHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="project_id" value="{$project.id}" />
              {$dataGridImages}
            </fieldset>
            </form>
          </div>
        {/option:dataGridImages}
      </div>
      {option:!dataGridImages}<p>{$msgNoProjectImages}</p>{/option:!dataGridImages}
		</div>
    
		<div id="tabFiles">
     <!-- change option name to showProjectsAddFile -->
     {option:showProjectsAddImage}
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_file'}&amp;project_id={$project.id}" class="button icon iconAdd" title="{$lblAddFile|ucfirst}">
					<span>{$lblAddFile|ucfirst}</span>
				</a>
			</div>
			{/option:showProjectsAddImage}
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProjectFilesHolder">
        {option:dataGridFiles}
          <div class="dataGridFilesHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="project_id" value="{$project.id}" />
              {$dataGridFiles}
            </fieldset>
            </form>
          </div>
        {/option:dataGridFiles}
      </div>
      {option:!dataGridFiles}<p>{$msgNoProjectFiles}</p>{/option:!dataGridFiles}
    </div>
    
    <div id="tabVideos">
     <!-- change option name to showProjectsAddVideo -->
     {option:showProjectsAddImage}
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_video'}&amp;project_id={$project.id}" class="button icon iconAdd" title="{$lblAddVideo|ucfirst}">
					<span>{$lblAddVideo|ucfirst}</span>
				</a>
			</div>
			{/option:showProjectsAddImage}
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProjectVideosHolder">
        {option:dataGridVideos}
          <div class="dataGridVideosHolder">
            <form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="project_id" value="{$project.id}" />
              {$dataGridVideos}
            </fieldset>
            </form>
          </div>
        {/option:dataGridVideos}
      </div>
      {option:!dataGridVideos}<p>{$msgNoProjectVideos}</p>{/option:!dataGridVideos}
    </div>
</div>

<div class="fullwidthOptions">
	<a href="{$var|geturl:'index'}" class="button">
		<span>{$lblBackToOverview|ucfirst}</span>
	</a>
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}