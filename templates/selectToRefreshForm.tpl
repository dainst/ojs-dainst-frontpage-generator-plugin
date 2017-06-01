{strip}
{assign var="pageTitle" value="plugins.generic.dainstFrontmatter.title"}
{include file="common/header.tpl"}
{/strip}

<div id="selectToRefresh">



<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}
<h4>{translate key="article.plugins.generic.dainstFrontmatter.idlistjournal"}</h4>
<p>{translate key="article.plugins.generic.dainstFrontmatter.idlistTextjournal"}<br>
{selectJournal}</p>
<h4>{translate key="article.plugins.generic.dainstFrontmatter.idlistselected"}</h4>
<p>{translate key="article.plugins.generic.dainstFrontmatter.idlistText"}</p>
<textarea id="dfm_urlslist" name="id" class='dfmTextrea'></textarea>
<p>{translate key="article.plugins.generic.dainstFrontmatter.idsAre"}</p>
<input type="radio" name="type" value="galley" id="type1"><label for="type1">Galley</label><br>
<input type="radio" name="type" value="article" id="type2" selected="selected"><label for="type2">Article</label> <p>{translate key="article.plugins.generic.dainstFrontmatter.articlealert"}</p><br>
<input type="radio" name="type" value="issue" id="type4"><label for="type4">Issue</label><br>
<input type="radio" name="type" value="journal" id="type3"><label for="type3">Journal</label><br>
<br/>
<input type="checkbox" name="replace" checked="checked" id="checkReplace"><label for="checkReplace">{translate key="article.plugins.generic.dainstFrontmatter.button.replace"}</label><br>
<br/>
<input type="submit" name="save" class="button defaultButton" value="{translate key="plugins.generic.dainstFrontmatter.button.update"}"/>
<input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

</div>
{include file="common/footer.tpl"}
