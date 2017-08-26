{strip}
{assign var="pageTitle" value="plugins.generic.dainstFrontmatter.title"}
{include file="common/header.tpl"}
{/strip}

<div id="selectToRefresh">
    <form method="post" action="{plugin_url path="generate"}">
        {include file="common/formErrors.tpl"}
        {include file="$thePath/article_picker/article_picker.tpl"}

        <input type="checkbox" name="replace" checked="checked" id="checkReplace"><label for="checkReplace">{translate key="plugins.generic.dainstFrontmatter.button.replace"}</label><br>
        <br/>
        <input type="submit" name="save" class="button defaultButton" value="{translate key="plugins.generic.dainstFrontmatter.button.update"}"/>
        <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
    </form>

</div>
{include file="common/footer.tpl"}
