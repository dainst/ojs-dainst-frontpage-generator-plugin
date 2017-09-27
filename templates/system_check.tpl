{strip}
    {assign var="pageTitle" value="plugins.generic.dainstFrontmatter.systemCheck"}
    {include file="common/header.tpl"}
{/strip}
{plugin_menu selected="systemcheck"}
<div class='dainstPluginLog'>
    {themResults}
</div>
<br/>

<input type="button" class="button" value="{translate key="common.back"}" onclick="history.go(-1)"/>


{include file="common/footer.tpl"}