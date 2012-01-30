$("#nsm_addon_updater_bak").each(function(index) {
	var $acc = $(this),
		$content = $("#nsm_addon_updater_content"),
		url = EE.BASE + '&C=addons_accessories&M=process_request' + 
				'&accessory=nsm_addon_updater&method=process_ajax_feeds';
	$.ajax({
		url: url,
		success: function(data) {
			$data = $(data);
			// no correct return data? exit
			if( $data[0].id !== "nsm_addon_updater_ajax_return") {
				return false;
			}
			else
			{
				$target.show();
				$trigger.addClass('active').data("active", true);
				$trigger.parent().addClass('active');
				$header.attr('rowspan', 2);
			}
			return false;
		});
	$updates = $("tbody tr th", $(this));
	$("#accessoryTabs .nsm_addon_updater").append("<span class='badge'>"+$updates.length+"</span>");
}); 
