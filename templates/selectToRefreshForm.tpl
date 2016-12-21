{strip}
{assign var="pageTitle" value="plugins.themes.dainst.colorsheme"}
{include file="common/header.tpl"}
{/strip}

<div id="selectToRefresh">

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}


<label>GalleyId:<input type="number" name="id"></label>
<input type="hidden" name="type" value="galley">

<br/>


<input type="submit" name="save" class="button defaultButton" value="REFRESH COVER PAGE"/>
<input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

</div>
{include file="common/footer.tpl"}
