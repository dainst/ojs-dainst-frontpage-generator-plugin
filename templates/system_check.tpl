{strip}
    {assign var=pageTitle value="plugins.generic.dainstFrontmatter.resultsTitle"}
    {include file="common/header.tpl"}
{/strip}
<h4>{translate key="plugins.generic.dainstFrontmatter.systemCheck"}</h4>
<div class='dainstPluginLog'>
    {themResults}
</div>
<br/>

<input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>


{include file="common/footer.tpl"}