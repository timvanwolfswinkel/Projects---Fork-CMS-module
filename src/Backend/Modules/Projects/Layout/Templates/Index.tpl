{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblProjects|ucfirst}</h2>

	{option:showProjectsAdd}
	<div class="buttonHolderRight">
		<a href="{$var|geturl:'add'}" class="button icon iconAdd" title="{$lblAddProject|ucfirst}">
			<span>{$lblAddProject|ucfirst}</span>
		</a>
	</div>
	{/option:showProjectsAdd}
</div>

<div id="dataGridProjectsHolder">
	{option:dataGrids}
		{iteration:dataGrids}
			<div class="dataGridHolder" id="dataGrid-{$dataGrids.id}">
				<div class="tableHeading clearfix">
					<h3>{$dataGrids.title}</h3>
				</div>
				{option:dataGrids.content}
					{$dataGrids.content}
				{/option:dataGrids.content}

				{option:!dataGrids.content}
					{$emptyDatagrid}
				{/option:!dataGrids.content}
			</div>
		{/iteration:dataGrids}
	{/option:dataGrids}
</div>

{option:!dataGrids}
	<p>{$msgNoProjects}</p>
{/option:!dataGrids}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}