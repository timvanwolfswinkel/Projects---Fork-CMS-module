{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblProjects|ucfirst}: {$msgEditClient|sprintf:{$item.title}}</h2>
</div>

{form:editClient}
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table width="100%">
				<tr>
					<td>
						<p>
							<label for="title">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtTitle} {$txtTitleError}
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<p>
							<label for="logo">{$lblLogo|ucfirst}</label>
								{option:item.image}
									<p><img src="{$FRONTEND_FILES_URL}/Projects/references/150x150/{$item.image}"/></p>
								{/option:item.image}
								<p>
									{$fileImage} {$fileImageError}
								</p>
						</p>
					</td>
				</tr>				
			</table>
		</div>

		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		{option:showProjectsDeleteClient}
			<a href="{$var|geturl:'delete_client'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
				<span>{$lblDelete|ucfirst}</span>
			</a>
			<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
				<p>
					{$msgConfirmDeleteClient|sprintf:{$item.title}}
				</p>
			</div>
		{/option:showProjectsDeleteClient}

		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:editClient}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}