{strip}
    {assign var="pageTitle" value="plugins.generic.dainstFrontmatter.settings"}
    {include file="common/header.tpl"}
{/strip}

{plugin_menu selected="settings"}

<div id="dfmSettings">
    <form method="post" action="{plugin_url path="settings"}">
        {include file="common/formErrors.tpl"}
        <br>


        <p>{translate key="plugins.generic.dainstFrontmatter.selectTheme"}</p>
        {foreach from=$settings.registry.themes item=theme }

            <input
                    type="radio"
                    name="dfm_theme"
                    id="check-theme-{$theme}"
                    value="{$theme}"
                    {if ($theme eq $settings.theme)}checked{/if}
                    {if (!$theme|get_availability)}disabled{/if}
            >
            <label for="check-theme-{$theme}">{$theme|get_title}</label><br>
        {/foreach}

        <br>
        <input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/>
        <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>

    </form>
</div>


{include file="common/footer.tpl"}