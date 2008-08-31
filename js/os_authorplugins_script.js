jQuery(document).ready(function() {
jQuery("#authorplugins-start").click(function() {
	jQuery("#authorplugins-wrap").hide();
	jQuery.ajax({ 
  		dataType: 'jsonp',
  		jsonp: 'jsonp_callback',
  		url: 'http://extend.schloebe.de/?format=json&q=wordpress',
  		success: function (j) {
			jQuery.each(j.plugins, function(i,plugin) {
				jQuery('#authorpluginsul').append( '<li><a href="' + plugin.link + '" target="_blank"><span class="post">' + plugin.title + '</span><span class="hidden"> - </span><cite>version ' + plugin.version + '</cite></a></li>' ).css("display", "none").fadeIn("slow");
    		});
		}
	});
});
});