<?php
if (!defined('ABSPATH')) {
	die('-1');
}
?>

<div id="rt_hcms_dashboard">
	<h2><?php echo esc_html(get_admin_page_title()); ?></h2>

	<form method="post" id="rt_hcms_values_table" action="options.php">
		<?php
		settings_fields('rt_hcms_settings_dashboard');
		do_settings_sections('rt_hcms_settings_dashboard');
		$all_post_types = get_option('all_post_types') ?: [];
		?>

		<div class="rt_hcms_text_row_full">
			<label for="domain_prefix_backend">
				Backend domain prfix
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A backend prefix to be used in the filtering function. Should be ended with the dot and create a valid URL particle with the <b>Domain name</b></p>
					</div>
				</div>
			</label>

			<input type="text" name="domain_prefix_backend" value="<?php echo esc_attr(get_option('domain_prefix_backend')); ?>" placeholder="backend.staging." />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="domain_prefix_frontend">
				Frontend domain prfix
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A frontend prefix to be used in the filtering function. Should be ended with the dot and create a valid URL particle with the <b>Domain name</b></p>
					</div>
				</div>
			</label>

			<input type="text" name="domain_prefix_frontend" value="<?php echo esc_attr(get_option('domain_prefix_frontend')); ?>" placeholder="staging." />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="domain_name">
				Domain name
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A base name of a site to be used in the filtering function</p>
					</div>
				</div>
			</label>

			<input type="text" name="domain_name" value="<?php echo esc_attr(get_option('domain_name')); ?>" placeholder="sitename.com" />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="frontend_url_base">
				Frontend URL base
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A frontpage URL of a current environment frontend. Used for the revalidation on demand. Input WITHOUT the end slash</p>
					</div>
				</div>
			</label>

			<input type="text" name="frontend_url_base" value="<?php echo esc_attr(get_option('frontend_url_base')); ?>" placeholder="https://sitename.com" />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="seo_sitename">
				SEO sitename
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A title of the site, which is about to be used for the opengraph (og) meta</p>
					</div>
				</div>
			</label>
			<input type="text" name="seo_sitename" value="<?php echo esc_attr(get_option('seo_sitename')); ?>" />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="breadcrumbs_homepage_name">
				Breadcrumbs homepage name
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A title of the homepage, which is about to be used in the breadcrumbs</p>
					</div>
				</div>
			</label>
			<input type="text" name="breadcrumbs_homepage_name" value="<?php echo esc_attr(get_option('breadcrumbs_homepage_name')); ?>" />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="custom_date_format">
				Date format
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>A format of the date response. Defaults to ''</p>
					</div>
				</div>
			</label>
			<input type="text" name="custom_date_format" value="<?php echo esc_attr(get_option('custom_date_format')); ?>" placeholder="Y-m-d" />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="include_authors_in_sitemap">
				Include authors in sitemap
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>If checked, the authors sitemap will be created, along with the post types sitemaps</p>
					</div>
				</div>
			</label>
			<input type="checkbox" name="include_authors_in_sitemap" <?php if (get_option('include_authors_in_sitemap')) echo 'checked'; ?> />
		</div>

		<div class="rt_hcms_text_row_full">
			<label for="include_categories_in_sitemap">
				Include categories in sitemap
				<div class="rt_hcms_how_to_wrapper">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>If checked, the categories sitemap will be created, along with the post types sitemaps</p>
					</div>
				</div>
			</label>
			<input type="checkbox" name="include_categories_in_sitemap" <?php if (get_option('include_categories_in_sitemap')) echo 'checked'; ?> />
		</div>

		<hr />

		<div id="rt_hcms_all_post_types">
			<h3>
				All post types
				<div class="rt_hcms_how_to_wrapper inline">
					<span class="dashicons dashicons-editor-help"></span>
					<div class="rt_hcms_how_to_text">
						<p>All post types to be run through the loops</p>
					</div>
				</div>
			</h3>

			<div class="rt_hcms_arr_wrapper">
				<?php for ($i = 0; $i < count($all_post_types); $i++) : ?>
					<div class="row" data-index="<?php echo $i; ?>" data-name="all_post_types">
						<input type="text" name='all_post_types[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('all_post_types')[$i]); ?>" />
						<button class="rt_hcms_remove_item" title="Remove this item">
							<span class="dashicons dashicons-dismiss"></span>
						</button>
					</div>
				<?php endfor; ?>

				<?php if (!count($all_post_types)) : ?>
					<div class="row" data-index="0" data-name="all_post_types">
						<input type="text" name='all_post_types[0]' value="<?php echo esc_attr(get_option('all_post_types')[0]); ?>" />
						<button class="rt_hcms_remove_item" title="Remove this item">
							<span class="dashicons dashicons-dismiss"></span>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<button id="add_new_post_type" class="button button-primary">Add new</button>
		</div>

		<hr />
		<h3>Outlink fields</h3>
		<div class="rt_hcms_outlinks_wrapper">
			<div class="rt_hcms_text_row_full">
				<label for="outlink_prefix">
					Outlink prefix
				</label>
				<input type="text" name="outlink_prefix" value="<?php echo esc_attr(get_option('outlink_prefix')); ?>" placeholder="go" />
			</div>

			<div class="rt_hcms_text_row_full">
				<label for="tc_outlink_prefix">
					TC outlink prefix
				</label>
				<input type="text" name="tc_outlink_prefix" value="<?php echo esc_attr(get_option('tc_outlink_prefix')); ?>" placeholder="tc" />
			</div>

			<div class="rt_hcms_text_row_full">
				<label for="affiliate_field_name">
					Affiliate field name
					<div class="rt_hcms_how_to_wrapper">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>A name of the casino affiliate link field, where the custom link will be put in case of the custom links usage</p>
						</div>
					</div>
				</label>
				<input type="text" name="affiliate_field_name" value="<?php echo esc_attr(get_option('affiliate_field_name')); ?>" placeholder="affiliate_link" />
			</div>

			<div class="rt_hcms_text_row_full">
				<label for="tc_affiliate_field_name">
					Affiliate field name
					<div class="rt_hcms_how_to_wrapper">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>A name of the casino TC affiliate link field, where the custom link will be put in case of the custom TC links usage</p>
						</div>
					</div>
				</label>
				<input type="text" name="tc_affiliate_field_name" value="<?php echo esc_attr(get_option('tc_affiliate_field_name')); ?>" placeholder="tc_affiliate_link" />
			</div>

			<div class="rt_hcms_text_row_full">
				<label for="custom_affiliate_field_name">
					Custom affiliate field name
					<div class="rt_hcms_how_to_wrapper">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>A name of the casino custom affiliate repeater</p>
						</div>
					</div>
				</label>
				<input type="text" name="custom_affiliate_field_name" value="<?php echo esc_attr(get_option('custom_affiliate_field_name')); ?>" placeholder="go_url_external_custom" />
			</div>

			<div class="rt_hcms_text_row_full">
				<label for="custom_tc_affiliate_field_name">
					Custom TC affiliate field name
					<div class="rt_hcms_how_to_wrapper">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>A name of the casino custom TC affiliate repeater</p>
						</div>
					</div>
				</label>
				<input type="text" name="custom_tc_affiliate_field_name" value="<?php echo esc_attr(get_option('custom_tc_affiliate_field_name')); ?>" placeholder="tc_url_external_custom" />
			</div>

			<?php $outlink_post_types = get_option('outlink_post_types') ?: []; ?>
			<div id="rt_hcms_outlink_post_types">
				<div class="paragraph">
					Outlink post types
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>Post types, which contain the outlinks data. Will be looped through during the search for an affiliate link bu slug</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($outlink_post_types); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="outlink_post_types">
							<input type="text" name='outlink_post_types[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('outlink_post_types')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($outlink_post_types)) : ?>
						<div class="row" data-index="0" data-name="outlink_post_types">
							<input type="text" name='outlink_post_types[0]' value="<?php echo esc_attr(get_option('outlink_post_types')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_outlink_post_type" class="button button-primary">Add new</button>
				<hr />
			</div>

			<?php $tc_outlink_post_types = get_option('tc_outlink_post_types') ?: []; ?>
			<div id="rt_hcms_tc_outlink_post_types">
				<div class="paragraph">
					TC outlink post types
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>Post types, which contain the TC outlinks data. Will be looped through during the search for an affiliate link bu slug</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($tc_outlink_post_types); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="tc_outlink_post_types">
							<input type="text" name='tc_outlink_post_types[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('tc_outlink_post_types')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($tc_outlink_post_types)) : ?>
						<div class="row" data-index="0" data-name="tc_outlink_post_types">
							<input type="text" name='tc_outlink_post_types[0]' value="<?php echo esc_attr(get_option('tc_outlink_post_types')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_tc_outlink_post_type" class="button button-primary">Add new</button>
				<hr />
			</div>

			<?php $outlink_meta_keys = get_option('outlink_meta_keys') ?: []; ?>
			<div id="rt_hcms_outlink_meta_keys">
				<div class="paragraph">
					Outlink meta keys
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>All the possible field names, which contain data about the outlink slugs. Used in the search through the database</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($outlink_meta_keys); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="outlink_meta_keys">
							<input type="text" name='outlink_meta_keys[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('outlink_meta_keys')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($outlink_meta_keys)) : ?>
						<div class="row" data-index="0" data-name="outlink_meta_keys">
							<input type="text" name='outlink_meta_keys[0]' value="<?php echo esc_attr(get_option('outlink_meta_keys')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_outlink_meta_key" class="button button-primary">Add new</button>
				<hr />
			</div>

			<?php $tc_outlink_meta_keys = get_option('tc_outlink_meta_keys') ?: []; ?>
			<div id="rt_hcms_tc_outlink_meta_keys">
				<div class="paragraph">
					TC outlink meta keys
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>All the possible field names, which contain data about the TC outlink slugs. Used in the search through the database</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($tc_outlink_meta_keys); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="tc_outlink_meta_keys">
							<input type="text" name='tc_outlink_meta_keys[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('tc_outlink_meta_keys')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($outlink_meta_keys)) : ?>
						<div class="row" data-index="0" data-name="tc_outlink_meta_keys">
							<input type="text" name='tc_outlink_meta_keys[0]' value="<?php echo esc_attr(get_option('tc_outlink_meta_keys')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_tc_outlink_meta_key" class="button button-primary">Add new</button>
				<hr />
			</div>

			<?php $outpage_casino_fields = get_option('outpage_casino_fields') ?: []; ?>
			<div id="rt_hcms_outpage_casino_fields">
				<div class="paragraph">
					Outpage casino fields
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>All the casino felds to be included at the outpage (except "logo", as it's included by default). Should be affiliate field name, custom affiliate and anything else to be displayed at the outpage</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($outpage_casino_fields); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="outpage_casino_fields">
							<input type="text" name='outpage_casino_fields[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('outpage_casino_fields')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($outpage_casino_fields)) : ?>
						<div class="row" data-index="0" data-name="outpage_casino_fields">
							<input type="text" name='outpage_casino_fields[0]' value="<?php echo esc_attr(get_option('outpage_casino_fields')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_outpage_casino_field" class="button button-primary">Add new</button>
			</div>

			<?php $tc_outpage_casino_fields = get_option('tc_outpage_casino_fields') ?: []; ?>
			<div id="rt_hcms_tc_outpage_casino_fields">
				<div class="paragraph">
					TC outpage casino fields
					<div class="rt_hcms_how_to_wrapper inline">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="rt_hcms_how_to_text">
							<p>All the casino felds to be included at the TC outpage (except "logo", as it's included by default). Should be TC affiliate field name, probably TC custom affiliate and anything else to be displayed at the TC outpage</p>
						</div>
					</div>
				</div>
				<div class="rt_hcms_arr_wrapper">
					<?php for ($i = 0; $i < count($tc_outpage_casino_fields); $i++) : ?>
						<div class="row" data-index="<?php echo $i; ?>" data-name="tc_outpage_casino_fields">
							<input type="text" name='tc_outpage_casino_fields[<?php echo $i; ?>]' value="<?php echo esc_attr(get_option('tc_outpage_casino_fields')[$i]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endfor; ?>

					<?php if (!count($tc_outpage_casino_fields)) : ?>
						<div class="row" data-index="0" data-name="tc_outpage_casino_fields">
							<input type="text" name='tc_outpage_casino_fields[0]' value="<?php echo esc_attr(get_option('tc_outpage_casino_fields')[0]); ?>" />
							<button class="rt_hcms_remove_item" title="Remove this item">
								<span class="dashicons dashicons-dismiss"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<button id="add_tc_outpage_casino_field" class="button button-primary">Add new</button>
			</div>
		</div>

		<?php submit_button(); ?>
	</form>
</div>