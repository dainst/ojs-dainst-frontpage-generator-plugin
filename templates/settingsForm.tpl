{strip}
    {assign var="pageTitle" value="plugins.generic.dainstFrontmatter.settings"}
    {include file="common/header.tpl"}
{/strip}

{plugin_menu selected="settings"}

<div id="dfmSettings">
    <form method="post" action="{plugin_url path="settings"}">
        {include file="common/formErrors.tpl"}
        <br>


        <p><strong>{translate key="plugins.generic.dainstFrontmatter.selectTheme"}</strong></p>
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

        <hr>
        <p><strong>{translate key="plugins.generic.dainstFrontmatter.thumbMode"}</strong></p>
        {foreach from=$settings.registry.thumbnailmodes item=thmode }

            <input
                    type="radio"
                    name="dfm_thumbmode"
                    id="check-thmode-{$thmode}"
                    value="{$thmode}"
                    {if ($thmode eq $settings.thumbMode)}checked{/if}
            >
            <label for="check-thmode-{$thmode}">{$thmode|get_title}</label><br>
        {/foreach}
        <input
                type="radio"
                name="dfm_thumbmode"
                id="check-thmode-none"
                value="none"
                {if ('none' eq $settings.thumbMode)}checked{/if}
        >
        <label for="check-thmode-none">none</label><br>

        <br>
        <input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/>
        <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>

    </form>
</div>


{include file="common/footer.tpl"}