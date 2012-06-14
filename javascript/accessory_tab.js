
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
			
			$content.html($data);
			$acc.find("a.note-trigger")
				.data("active", false)
				.click(function() {
					$trigger = $(this);
					$row = $(this).parent().parent();
					$header = $row.find("th");
					$target = $row.next();
					if ($trigger.data('active')) {
						$target.hide();
						$trigger.removeClass('active').data("active", false);
						$trigger.parent().removeClass('active');
						$header.attr('rowspan', 1);
					} else {
						$target.show();
						$trigger.addClass('active').data("active", true);
						$trigger.parent().addClass('active');
						$header.attr('rowspan', 2);
					}
					return false;
				});
			$updates = $("tbody tr th", $acc);
			$("#accessoryTabs .nsm_addon_updater").append("<span class='badge'>"+$updates.length+"</span>");
			
		},
		error: function() {
			$content.addClass('alert error').text('There was an error retrieving the update feeds.');
		}
		
	});
	$updates = $("tbody tr th", $(this));
	$("#accessoryTabs .nsm_addon_updater").append("<span class='badge'>"+$updates.length+"</span>");
});


$("#nsm_addon_updater").each(function(index) {
	var $acc			= $(this),
		$content		= $("#nsm_addon_updater_content"),
		url				= EE.BASE + '&C=addons_accessories&M=process_request' + 
			'&accessory=nsm_addon_updater&method=process_ajax_version_request' + 
			'&addon_id=',
		$addon_rows		= $content.find('table tbody tr'),
		count			= 0,
		total_addons	= $addon_rows.length;
	
	$addon_rows.each(function() {
		var $self		= $(this),
			addon_id	= $self.data('nsm_addon_updater_addon_id');
		
		$.ajax({
			url: url + addon_id,
			dataType: 'json',
			success: function(data) {
				if (data.is_current == true) {
					$self.addClass('success');
				} else {
					$self.addClass('info');
				}
				
				for (var key in data) {
					$self
						.find('span[data-nsm_addon_updater_cell="'+key+'"]')
						.text(data[key]);
				}
				
				count += 1;
				
				if (count == total_addons) {
					$acc.find('div.loader').remove();
				}
				
			},
			error: function(data) {
				$self
					.addClass('error')
					.find('span[data-nsm_addon_updater_cell="status"]')
					.text(data.responseText);
					
				count += 1;
					
				if (count == total_addons) {
					$acc.find('div.loader').remove();
				}
			}
		});
	});
	
});
