jQuery(document).ready(function() {
	jQuery('#dfm_urlslist').keyup(function() {
		dfmTimeout = setTimeout(dfmExtract, 1500);
	});
	
	
	jQuery('#dfm_journalselect').change(function() {
		if (jQuery('#dfm_journalselect').val() != -1) {
			jQuery('#dfm_urlslist').val(jQuery('#dfm_journalselect').val());
			jQuery('#type3').click();
		}

	});
});

var dfmTimeout;

function dfmExtract(str) {
	var str = jQuery('#dfm_urlslist').val();

	if (/^(\d+\s*,\s*)+\d*\s*,?\s*$/.test(str)) {
		return;
	}
	
	
	var m;
	var numbers = [];
	const regex = /(.+view\/)?(\d+)(\/(\d+))?/g;
	while ((m = regex.exec(str)) !== null) {
	    // This is necessary to avoid infinite loops with zero-width matches
	    if (m.index === regex.lastIndex) {
	        regex.lastIndex++;
	    }
	    if (typeof m[4] !== "undefined") { // we have galley numbers
		    numbers.push(m[4]);
		    jQuery('#type1').click();
	    } else if ((typeof m[2] !== "undefined") && (typeof m[1] !== "undefined")) { // we have article ids
	    	numbers.push(m[2])
	    	jQuery('#type2').click();
	    } else if (typeof m[2] !== "undefined") { // we have some other ids
		    numbers.push(m[2])
	    }
	}
	jQuery('#dfm_urlslist').val(numbers.join(', '));
}