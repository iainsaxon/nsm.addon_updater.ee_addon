
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
		total_addons	= $addon_rows.length,
		count			= 0,
		badge_count		= 0;
	
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
					badge_count += 1;
				}
				
				for (var key in data) {
					var $target = $self.find('span[data-nsm_addon_updater_cell="'+key+'"]');
					switch (key) {
						case 'docs_url':
							if (data[key] !== false) {
								$target.html('<a href="'+data[key]+'" rel="external">Visit site</a>');
							}
							break;
						case 'download':
							if (data[key] !== false) {
								$target.html('<a href="'+data[key]+'" rel="external">Download</a>');
							}
							break;
						case 'notes':
							if (data[key] !== false) {
								$self
									.find('span[data-nsm_addon_updater_cell="notes_trigger"]')
									.html('<a href="#" class="note-trigger">Release notes</a>');
								
								var notes_html = '' +
									'<tr class="' + ($self.hasClass('odd') ? 'odd' : 'even') + '" style="display:none">' +
										'<td colspan="6">' +
											'<h3>' + data.title + '</h3>' +
											'<p>Published: ' + data.created_at[0] + '</p>' +
											data.notes +
										'</td>' +
									'</tr>';
								
								$self.after(notes_html);
								
								$self
									.find("a.note-trigger")
									.data("active", false)
									.click(function() {
										$notes_trigger = $(this);
										$row = $self;
										$header = $row.find("th");
										$notes_target = $row.next();
										if ($notes_trigger.data('active')) {
											$notes_target.hide();
											$notes_trigger.removeClass('active').data("active", false);
											$notes_trigger.parent().removeClass('active');
											$header.attr('rowspan', 1);
										} else {
											$notes_target.show();
											$notes_trigger.addClass('active').data("active", true);
											$notes_trigger.parent().addClass('active');
											$header.attr('rowspan', 2);
										}
										return false;
									});
								
							}
							break;
						default:
							$target.text(data[key]);
							break;
					}
				}
				
				count += 1;
				
				if (count == total_addons) {
					$acc.find('div.loader').remove();
					$("#accessoryTabs .nsm_addon_updater").append("<span class='badge'>"+badge_count+"</span>");
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
					$("#accessoryTabs .nsm_addon_updater").append("<span class='badge'>"+badge_count+"</span>");
				}
			}
		});
	});
	
});
