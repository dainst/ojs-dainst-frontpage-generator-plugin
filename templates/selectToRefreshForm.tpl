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

        <input type="radio" name="frontmatters" id="checkCreate" value="create">
        <label for="checkCreate">{translate key="plugins.generic.dainstFrontmatter.button.createfm"}</label>
        <br>
        <input type="radio" name="frontmatters" id="checkReplace" value="replace">
        <label for="checkReplace">{translate key="plugins.generic.dainstFrontmatter.button.replacefm"}</label>
        <br>
        <input type="radio" name="frontmatters" checked="checked" id="keep" value="keep">
        <label for="keep">{translate key="plugins.generic.dainstFrontmatter.button.keepfm"}</label>
        <br>
        <input type="checkbox" name="thumbnails" id="checkThumbnail">
        <label for="checkThumbnail">{translate key="plugins.generic.dainstFrontmatter.button.thumbnail"}</label>

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
