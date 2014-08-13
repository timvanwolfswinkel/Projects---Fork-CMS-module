{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblProjects|ucfirst}: {$lblImages}</h2>

	{option:showProjectsAddImage}
	<div class="buttonHolderRight">
		<a href="{$var|geturl:'add_image'}&amp;project_id={$project.id}" class="button icon iconAdd" title="{$lblAddImage|ucfirst}">
			<span>{$lblAddImage|ucfirst}</span>
		</a>
	</div>
	{/option:showProjectsAddImage}
</div>

<div id="dataGridProjectsHolder">
	{option:dataGrid}
		<div class="dataGridHolder">
			<form action="{$var|geturl:'mass_action'}" method="get" class="forkForms submitWithLink" id="massAction">
			<fieldset>
				<input type="hidden" name="project_id" value="{$project.id}" />
				{$dataGrid}
			</fieldset>
			</form>
		</div>
	{/option:dataGrid}
</div>
{option:!dataGrid}<p>{$msgNoItems}</p>{/option:!dataGrid}

<div class="fullwidthOptions">
	<a href="{$var|geturl:'index'}" class="button">
		<span>{$lblBackToOverview|ucfirst}</span>
	</a>
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}