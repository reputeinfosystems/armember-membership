<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Fetch plans for dropdowns */
global $wpdb, $ARMemberLite, $arm_subscription_plans;

$plans = array();
if ( isset( $ARMemberLite ) && isset( $ARMemberLite->tbl_arm_subscription_plans ) ) {
	$tbl   = $ARMemberLite->tbl_arm_subscription_plans;
	$plans = $wpdb->get_results( "SELECT arm_subscription_plan_id, arm_subscription_plan_name FROM `{$tbl}` ORDER BY arm_subscription_plan_name ASC" ); // phpcs:ignore
}
?>
<div class="wrap arm-csv-ie-wrap">
	<h1><?php esc_html_e( 'CSV Import &amp; Export', 'arm-csv-import-export' ); ?></h1>

	<!-- ===== TAB NAV ===================================================== -->
	<nav class="nav-tab-wrapper arm-csv-ie-tabs" id="armCsvTabs">
		<a href="#arm-csv-export" class="nav-tab nav-tab-active" data-tab="export">
			<?php esc_html_e( 'Export Members', 'arm-csv-import-export' ); ?>
		</a>
		<a href="#arm-csv-import" class="nav-tab" data-tab="import">
			<?php esc_html_e( 'Import Members', 'arm-csv-import-export' ); ?>
		</a>
	</nav>

	<!-- ===== EXPORT PANEL ================================================ -->
	<div id="arm-csv-export" class="arm-csv-ie-panel">
		<div class="arm-csv-ie-card">
			<h2><?php esc_html_e( 'Export Members to CSV', 'arm-csv-import-export' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Filter members and download them as a CSV file. Leave filters blank to export all members.', 'arm-csv-import-export' ); ?>
			</p>

			<form id="armCsvExportForm">
				<table class="form-table arm-csv-ie-table">
					<tr>
						<th scope="row">
							<label for="arm_export_plans"><?php esc_html_e( 'Subscription Plan', 'arm-csv-import-export' ); ?></label>
						</th>
						<td>
							<select name="plan_ids[]" id="arm_export_plans" multiple class="arm-csv-ie-select">
								<?php foreach ( $plans as $plan ) : ?>
									<option value="<?php echo esc_attr( $plan->arm_subscription_plan_id ); ?>">
										<?php echo esc_html( $plan->arm_subscription_plan_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Hold Ctrl / Cmd to select multiple.', 'arm-csv-import-export' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="arm_export_status"><?php esc_html_e( 'Member Status', 'arm-csv-import-export' ); ?></label>
						</th>
						<td>
							<select name="status" id="arm_export_status" class="arm-csv-ie-select-single">
								<option value=""><?php esc_html_e( '— All statuses —', 'arm-csv-import-export' ); ?></option>
								<option value="1"><?php esc_html_e( 'Active', 'arm-csv-import-export' ); ?></option>
								<option value="0"><?php esc_html_e( 'Inactive', 'arm-csv-import-export' ); ?></option>
								<option value="2"><?php esc_html_e( 'Pending', 'arm-csv-import-export' ); ?></option>
								<option value="3"><?php esc_html_e( 'Expired', 'arm-csv-import-export' ); ?></option>
								<option value="4"><?php esc_html_e( 'Banned', 'arm-csv-import-export' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Registration Date', 'arm-csv-import-export' ); ?></th>
						<td>
							<label>
								<?php esc_html_e( 'From', 'arm-csv-import-export' ); ?>
								<input type="date" name="date_from" id="arm_export_date_from" class="arm-csv-ie-date">
							</label>
							&nbsp;&nbsp;
							<label>
								<?php esc_html_e( 'To', 'arm-csv-import-export' ); ?>
								<input type="date" name="date_to" id="arm_export_date_to" class="arm-csv-ie-date">
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Extra Columns', 'arm-csv-import-export' ); ?></th>
						<td>
							<label><input type="checkbox" name="extra_fields[]" value="first_name" checked> <?php esc_html_e( 'First Name', 'arm-csv-import-export' ); ?></label><br>
							<label><input type="checkbox" name="extra_fields[]" value="last_name" checked> <?php esc_html_e( 'Last Name', 'arm-csv-import-export' ); ?></label><br>
							<label><input type="checkbox" name="extra_fields[]" value="display_name"> <?php esc_html_e( 'Display Name', 'arm-csv-import-export' ); ?></label><br>
							<label><input type="checkbox" name="extra_fields[]" value="nickname"> <?php esc_html_e( 'Nickname', 'arm-csv-import-export' ); ?></label><br>
							<label><input type="checkbox" name="extra_fields[]" value="description"> <?php esc_html_e( 'Bio / Description', 'arm-csv-import-export' ); ?></label><br>
							<label><input type="checkbox" name="extra_fields[]" value="user_url"> <?php esc_html_e( 'Website URL', 'arm-csv-import-export' ); ?></label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" id="armCsvExportBtn" class="button button-primary">
						<?php esc_html_e( 'Export CSV', 'arm-csv-import-export' ); ?>
					</button>
					<span class="arm-csv-ie-spinner" id="armExportSpinner" style="display:none;">
						<span class="spinner is-active"></span>
						<?php esc_html_e( 'Exporting…', 'arm-csv-import-export' ); ?>
					</span>
				</p>
			</form>
		</div>
	</div>

	<!-- ===== IMPORT PANEL ================================================ -->
	<div id="arm-csv-import" class="arm-csv-ie-panel" style="display:none;">
		<div class="arm-csv-ie-card">
			<h2><?php esc_html_e( 'Import Members from CSV', 'arm-csv-import-export' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Upload a CSV file to create or update member accounts.', 'arm-csv-import-export' ); ?>
				<a href="#" id="armCsvDownloadSample">
					<?php esc_html_e( 'Download sample CSV', 'arm-csv-import-export' ); ?>
				</a>
			</p>

			<!-- Step 1: Upload -->
			<div id="armImportStep1">
				<form id="armCsvUploadForm" enctype="multipart/form-data">
					<table class="form-table arm-csv-ie-table">
						<tr>
							<th scope="row">
								<label for="arm_csv_file"><?php esc_html_e( 'CSV File', 'arm-csv-import-export' ); ?></label>
							</th>
							<td>
								<input type="file" name="csv_file" id="arm_csv_file" accept=".csv" required>
								<p class="description"><?php esc_html_e( 'Maximum file size: 5 MB. Accepted format: .csv', 'arm-csv-import-export' ); ?></p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary" id="armCsvUploadBtn">
							<?php esc_html_e( 'Upload &amp; Preview', 'arm-csv-import-export' ); ?>
						</button>
						<span class="arm-csv-ie-spinner" id="armUploadSpinner" style="display:none;">
							<span class="spinner is-active"></span>
							<?php esc_html_e( 'Parsing file…', 'arm-csv-import-export' ); ?>
						</span>
					</p>
				</form>
			</div>

			<!-- Step 2: Map columns & confirm -->
			<div id="armImportStep2" style="display:none;">
				<h3><?php esc_html_e( 'Map CSV Columns', 'arm-csv-import-export' ); ?></h3>
				<p class="description" id="armImportRowCount"></p>

				<div id="armColumnMapContainer"></div>

				<h3><?php esc_html_e( 'Import Options', 'arm-csv-import-export' ); ?></h3>
				<table class="form-table arm-csv-ie-table">
					<tr>
						<th scope="row">
							<label for="arm_import_plan"><?php esc_html_e( 'Assign Subscription Plan', 'arm-csv-import-export' ); ?></label>
						</th>
						<td>
							<select name="plan_id" id="arm_import_plan" class="arm-csv-ie-select-single">
								<option value="0"><?php esc_html_e( '— None —', 'arm-csv-import-export' ); ?></option>
								<?php foreach ( $plans as $plan ) : ?>
									<option value="<?php echo esc_attr( $plan->arm_subscription_plan_id ); ?>">
										<?php echo esc_html( $plan->arm_subscription_plan_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Duplicate Handling', 'arm-csv-import-export' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="arm_update_existing" name="update_existing" value="1">
								<?php esc_html_e( 'Update existing users (matched by email or username)', 'arm-csv-import-export' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Notifications', 'arm-csv-import-export' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="arm_send_notify" name="send_notify" value="1">
								<?php esc_html_e( 'Send welcome email to newly created users', 'arm-csv-import-export' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<!-- Preview table -->
				<h3><?php esc_html_e( 'Data Preview (first 5 rows)', 'arm-csv-import-export' ); ?></h3>
				<div id="armPreviewTableWrap" style="overflow-x:auto;"></div>

				<p class="submit">
					<button type="button" id="armCsvImportBtn" class="button button-primary">
						<?php esc_html_e( 'Import Members', 'arm-csv-import-export' ); ?>
					</button>
					<button type="button" id="armCsvImportBack" class="button">
						<?php esc_html_e( '&larr; Back', 'arm-csv-import-export' ); ?>
					</button>
					<span class="arm-csv-ie-spinner" id="armImportSpinner" style="display:none;">
						<span class="spinner is-active"></span>
						<?php esc_html_e( 'Importing…', 'arm-csv-import-export' ); ?>
					</span>
				</p>
			</div>

			<!-- Step 3: Results -->
			<div id="armImportStep3" style="display:none;">
				<h3><?php esc_html_e( 'Import Complete', 'arm-csv-import-export' ); ?></h3>
				<div id="armImportResults"></div>
				<p class="submit">
					<button type="button" id="armCsvImportAnother" class="button button-primary">
						<?php esc_html_e( 'Import Another File', 'arm-csv-import-export' ); ?>
					</button>
				</p>
			</div>
		</div>
	</div><!-- /#arm-csv-import -->

</div><!-- /.wrap -->
