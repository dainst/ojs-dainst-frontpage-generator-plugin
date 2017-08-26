<div id="as">
    <div id="as-tabs">
        <a href='#' id='as-tab-picker' class="as-tabs-selected">{translate key="plugins.generic.as.picker"}</a>
        <a href='#' id='as-tab-input' class="">{translate key="plugins.generic.as.manually"}</a>
    </div>
    <div id="as-mode-picker" class="as-mode" style="display:block">
        <div class="as-headline"></div>
        <select id="as-select"></select><br>
        <input id="as-filter" placeholder="filter">
        <ul id="as-idlist"></ul>
    </div>
    <div id="as-mode-input"  class="as-mode" style="display:none">
        <div class="as-headline">{translate key="plugins.generic.dainstFrontmatter.idlistText"}</div>
        <textarea id="as-input-idlist" name="idlist"></textarea>
        <div>{translate key="plugins.generic.as.idsAre"}</div>
        <div id="as-input-type">
            <input type="radio" name="type" value="galley"  id="as-input-type-galleys" ><label for="as-input-type-galleys" >Galleys</label>
            <input type="radio" name="type" value="article" id="as-input-type-articles"><label for="as-input-type-articles">Articles</label>
            <input type="radio" name="type" value="issue"   id="as-input-type-issues"  ><label for="as-input-type-issues"" >Issues</label>
            <input type="radio" name="type" value="journal" id="as-input-type-journals"><label for="as-input-type-journals">Journals</label>
        </div>
        <div>{translate key="plugins.generic.as.articlealert"}</div>
    </div>
</div>