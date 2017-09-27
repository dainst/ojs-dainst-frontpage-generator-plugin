{strip}
{assign var="pageTitle" value="plugins.generic.dainstFrontmatter.title"}
{include file="common/header.tpl"}
{/strip}

{plugin_menu selected="generate"}

<div id="selectToRefresh">
    <form method="post" action="{plugin_url path="generate"}">
        {include file="common/formErrors.tpl"}
        <br>

        {article_picker}


        <input type="checkbox" name="replace" checked="checked" id="checkReplace">
        <label for="checkReplace">{translate key="plugins.generic.dainstFrontmatter.button.replace"}</label>
        <br>
        <input type="checkbox" name="thumpnails" disabled id="checkThumpnail">
        <label for="checkReplace">{translate key="plugins.generic.dainstFrontmatter.button.thumpnail"}</label>

        {if ($settings.theme|get_availability)}
            <p>{translate key="plugins.generic.dainstFrontmatter.selectedTheme"}: {$settings.theme|get_title}</p>
            <input type="submit" name="save" class="button defaultButton" value='{translate key="plugins.generic.dainstFrontmatter.button.update"}' />
        {else}
            <p>{translate key="plugins.generic.dainstFrontmatter.selectedThemeNone"}</p>
        {/if}
        <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>


    </form>

</div>
{include file="common/footer.tpl"}
