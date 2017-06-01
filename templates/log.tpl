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


<input type="button" class="button" value="{translate key="common.ok"}" onclick="history.go(-1)"/>


{include file="common/footer.tpl"}