{**
 * plugins/generic/dataverse/templates/termsOfUse.tpl
 *
 * Copyright (c) 2013-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display terms of use of Dataverse configured for journal
 *
 *}
{strip}
{assign var=pageTitle value="plugins.generic.dainstFrontmatter.resultsTitle"}
{include file="common/header.tpl"}
{/strip}

<table id="dfm-log">
	<h4>{translate key="plugins.generic.dainstFrontmatter.testing"}</h4>
	<tr>
		<td>
			<input type="button" class="button" value="{translate key="common.back"}" onclick="history.go(-1)"/>
			<div class='dainstPluginLog'>
				<strong>{translate key="plugins.generic.dainstFrontmatter.protocol"}</strong>
				{$dfm_log}
			</div>
		</td>
		<td>
			<form method="post" action="{plugin_url path="settings"}" target="dfm-pdfpreview">
				<input type="hidden" name="workingdirectory" value="{$pdfpreview}"/>
				<input type="submit" class="button" name="previewpdf" value="{translate key="plugins.generic.dainstFrontmatter.seePreview"}" />
			</form>

			<iframe name="dfm-pdfpreview" src=""></iframe>
		</td>
	</tr>
</table>

{include file="common/footer.tpl"}