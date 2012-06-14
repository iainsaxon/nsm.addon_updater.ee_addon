<div class="mor acc" style="width:100%">
	<div class="tg">
		<h2>Available Updates</h2>
		<div id="nsm_addon_updater_content">
			<div class="alert info loader">Loading...</div>

			<table>
				<thead>
					<tr>
						<th scope="col">Addon</th>
						<th scope="col">Installed</th>
						<th scope="col">Latest</th>
						<th scope="col">Status</th>
						<th scope="col">&nbsp;</th>
						<th scope="col">&nbsp;</th>
						<th scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php $count = 0; foreach ($addons as $addon) : $class = ($count%2) ? "odd" : "even"; $count++; ?>
					<tr
						class="alert <?= $class; ?>"
						data-nsm_addon_updater_addon_id="<?= $addon['extension_class']; ?>"
					>
						<th scope="row">
							<?= $addon['addon_name']; ?>
						</th>
						<td>
							<span data-nsm_addon_updater_cell="installed_version">
								<?= $addon['installed_version']; ?>
							</span>
						</td>
						<td>
							<span data-nsm_addon_updater_cell="latest_version">
								-
							</span>
						</td>
						<td>
							<span data-nsm_addon_updater_cell="status">
								-
							</span>
						</td>
						<td>
							<span data-nsm_addon_updater_cell="notes_anchor">
								-
							</span>
						</td>
						<td>
							<span data-nsm_addon_updater_cell="docs_url">
								-
							</span>
						</td>
						<td>
							<span data-nsm_addon_updater_cell="download_url">
								-
							</span>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
		</div>
	</div>
</div>
