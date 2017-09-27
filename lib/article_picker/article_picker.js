(function($) {

    var picker = function() {

        var state = -1; // 0: journals, 1: issues, 2: articles, 3:issues
        var mode = -1;
        var states = ['journals', 'issues', 'articles', 'galleys'];
        var queries = ['journal', 'issue', 'article'];

        var isError = false;

        var current = {};

        var selected = [];

        var as_timeout;

        var titles = {
            galleys: {},
            articles: {},
            issues: {},
            journals: {}
        }

        var tabIsPicker = true;

        function getSet(id) {

            var data = {}
            data.task = states[state];
            if (typeof queries[state - 1] !== "undefined") {
                data[queries[state - 1]] = current[queries[state - 1]];
            }

            jQuery.post('api', data, fillMenu).fail(apiError);
        }

        function apiError() {
            isError = true;
            jQuery('#as-error').toggle(true);
            jQuery('#as-select').toggle(false);
            jQuery('#as-filter').toggle(false);
        }

        function start() {
            state = -1;
            mode = -1;
            jQuery('#as-filter').val('');
            fillMenuModes();
            refreshList();
        }

        function fillMenu(data) {

            if (data.success == false) {
                return apiError();
            }

            jQuery('#as-select').empty();

            var set = states[state];
            console.log('fill with',set,data);

            var title = jQuery('#as-select').append(jQuery('<li class="as-select-headline" value="#">' + set + '</li>'));
            if (state > 0) {
                jQuery('#as-select').append(jQuery('<li class="as-select-back as-btn" value="#">'+as_js_strings.goBack+'</li>'));
            }
            jQuery.each(data[set], function(k,v) {
                titles[set][k] = v;
                var option = jQuery('<li class="as-select-item" value="' + k + '">' + v + '</li>');
                if ((mode == state) && (selected.indexOf(k) !== -1)) {
                    jQuery(option).addClass('as-select-selected')
                }
                jQuery('#as-select').append(option);
            });
            //jQuery(title).attr('selected', true);
            jQuery('#as-filter').val('');
        }

        function fillMenuModes() {
            jQuery('#as-select').empty();
            jQuery('#as-select').append(jQuery('<li class="as-select-headline" value="#">'+as_js_strings.selectObjectType+'</li>'));
            jQuery.each(states, function(i, imode) {
                jQuery('#as-select').append(jQuery('<li class="as-select-mode as-select-item" value="' + i + '">select ' + imode + '</li>'));
            });
        }

        function filter(evt) {

            var filterbox = jQuery('#as-filter');
            var str  = jQuery(filterbox).val();
            var selectoid = false;
            jQuery('#as-select .as-select-item').each(function(k, opt) {
                var show = ((str === '') || (jQuery(opt).text().match(new RegExp(str)) !== null));
                jQuery(opt).css({'display': show ? 'block' : 'none'});
                selectoid = (!selectoid && show) ? opt : selectoid;
            });
            jQuery(selectoid).attr('selected', true);
            if ((typeof evt !== "undefined") && (evt.keyCode === 13)) { //enter
                evt.preventDefault();
                evt.stopImmediatePropagation();
                if (jQuery(filterbox).val() === jQuery(selectoid).text()) {
                    jQuery(filterbox).val('');
                    jQuery(selectoid).click();
                } else {
                    jQuery(filterbox).val(jQuery(selectoid).text());

                }
            }
        }


        function goBack() {

            delete current[queries[state]];
            console.log(state, current);
            if (state > 0) {
                state -= 1;
                getSet()
            }
        }

        function select(ev) {
            console.log(ev);
            var selection = jQuery(ev.target);
            if (selection.hasClass('as-select-headline')) {
                return;
            }
            if (selection.hasClass('as-select-back')) {
                console.log('back');
                return goBack();
            }

            var id = selection.val();

            if (typeof queries[state] !== "undefined") {
                current[queries[state]] = id;
            }

            console.log(state, mode);
            if (mode == -1) {
                selectMode(id);
            } else if (state < mode) {
                state += 1;
                getSet();
            } else if (state == mode) {
                selection.addClass('as-select-selected');
                add(id);
            }
            refreshList();
            refreshTextarea();

        }

        function selectMode(newmode) {
            mode = newmode;
            state = 0;
            console.log('new mode is', mode)
            getSet();
        }

        function add(id) {
            //selection.addClass('as-select-selected');
            selected.push(id);
            selected = arrayToUnique(selected);

        }

        function remove(id) {
            selected.splice(selected.indexOf(id),1);

            var selectedOption = jQuery('.as-select-item[value="' + id + '"]');
            console.log(selectedOption);
            if (selectedOption) {
                selectedOption.removeClass('as-select-selected');
            }

            if (selected.length === 0) {
                start();
            }

            refreshList();
            refreshTextarea();
        }

        function getTitle(set, id) {
            return titles[set][id] || set + '#' + id;
        }

        function refreshList() {

            // headline
            var text = 'Select ' + (states[mode] || as_js_strings.objectType);
            jQuery('#as-mode-picker .as-type-name').text(text);

            // list
            console.log(titles)
            jQuery('#as-idlist').empty();
            if (mode < 0) {
                return;
            }
            jQuery.each(selected, function(i, id) {
                console.log(id)
                var li = jQuery('<li>'+getTitle(states[mode],id)+'</li>');
                var btn = jQuery('<span class="as-btn" href="#">remove</span>');
                jQuery(btn).click(function() {
                    remove(id)
                });
                jQuery(li).append(btn);
                jQuery('#as-idlist').append(li);
            })
        }

        function refreshTextarea() {
            jQuery('#as-input-idlist').val(selected.join(','));
            jQuery('#as-input-type-' + states[mode]).attr('checked', true);
        }

        function arrayToUnique(a, b, c) { //array,placeholder,placeholder
            b = a.length;
            while (c = --b)
                while (c--) a[b] !== a[c] || a.splice(c, 1);
            return a // not needed ;)
        }



        function extractIdsFromInputBox() {
            var str = jQuery('#as-input-idlist').val();

            if ((str === '') || /^(\d+\s*,\s*)+\d*\s*,?\s*$/.test(str)) {
                return;
            }

            var m;
            var numbers = [];
            const regex = /(\/\w+\/)?(\d+)(\/(\d+))?/g;
            while ((m = regex.exec(str)) !== null) {
                console.log(m);
                // This is necessary to avoid infinite loops with zero-width matches
                if (m.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
                if (typeof m[4] !== "undefined") { // we have galley numbers
                    numbers.push(m[4]);
                    mode = 3;
                } else if ((typeof m[2] !== "undefined") && (typeof m[1] !== "undefined")) { // we have article ids
                    numbers.push(m[2]);
                    mode = 2;
                } else if (typeof m[2] !== "undefined") { // we have some other ids
                    numbers.push(m[2])
                    mode = -1;
                }
            }
            console.log(numbers);
            selected = arrayToUnique(numbers);
            refreshTextarea();
            jQuery('#as-input-idlist').val(numbers.join(', '));
        }

        function evaluateInputBox() {
            as_timeout = setTimeout(extractIdsFromInputBox, 1500);
        }

        function toggleTabs() {
            jQuery('#as-mode-input').toggle();
            jQuery('#as-mode-picker').toggle();
            jQuery('#as-tab-input').toggleClass('as-tabs-selected');
            jQuery('#as-tab-picker').toggleClass('as-tabs-selected');
            tabIsPicker = !tabIsPicker;
            if (tabIsPicker) {
                refreshList();
            } else {
                refreshTextarea();
            }
        }

        function toggleFilter() {
            jQuery('#as-filter').toggle();
        }

        jQuery(document).ready(function() {
            jQuery('#as-select').click(select);
            jQuery('#as-filter').keypress(filter);
            jQuery('#as-input-idlist').keyup(evaluateInputBox);
            jQuery('#as-tab-input').click(toggleTabs);
            jQuery('#as-tab-picker').click(toggleTabs);
            jQuery('#as-filter-button').click(toggleFilter);
            start();
        });
    }

    $.pkp.plugins.generic.article_picker = $.pkp.plugins.generic.article_picker || new picker();
}(jQuery));