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
<h4>Protocol</h4>
<label for="toggleDebugInfo" class="btn btn-xs btn-default">Show Debug Info</label><input id='toggleDebugInfo' type="checkbox">
<div class='dainstPluginLog'>
	{themResults}
</div>
<br/>




{if isset($continue_ids)}
	<form method="post" action="{plugin_url path="settings"}">
		<input type="hidden" name="type" value="galley">
		<input type="hidden" name="replace" value="{$continue_updateFrontpages}">
		<input type="hidden" name="id" value="{$continue_ids}">
		<input type="submit" name="save" class="button defaultButton" value="{translate key="common.continue"} ({$continue_left})"/>
	</form>
	<a class="btn btn-danger" href="{plugin_url path='settings'}">{translate key="common.cancel"}</a>
{else}
	<a class="btn btn-default" href="{plugin_url path='settings'}">{translate key="common.ok"}</a>
{/if}

{include file="common/footer.tpl"}