(function ($) {
	// Add new menu row to the translated menus
	const $allPostTypes = $('#rt_hcms_all_post_types');
	const $addNewPostTypeBtn = $allPostTypes.find('#add_new_post_type');
	const $outlinkPostTypes = $('#rt_hcms_outlink_post_types');
	const $addOutlinkPostTypeBtn = $outlinkPostTypes.find('#add_outlink_post_type');
	const $tcOutlinkPostTypes = $('#rt_hcms_tc_outlink_post_types');
	const $addTcOutlinkPostTypeBtn = $tcOutlinkPostTypes.find('#add_tc_outlink_post_type');
	const $outlinkMetaKeys = $('#rt_hcms_outlink_meta_keys');
	const $addOutlinkMetaKeyBtn = $outlinkMetaKeys.find('#add_outlink_meta_key');
	const $tcOutlinkMetaKeys = $('#rt_hcms_tc_outlink_meta_keys');
	const $addTcOutlinkMetaKeyBtn = $tcOutlinkMetaKeys.find('#add_tc_outlink_meta_key');
	const $outpageCasinoFields = $('#rt_hcms_outpage_casino_fields');
	const $addOutpageCasinoFieldBtn = $outpageCasinoFields.find('#add_outpage_casino_field');
	const $tcOutpageCasinoFields = $('#rt_hcms_tc_outpage_casino_fields');
	const $addTcOutpageCasinoFieldBtn = $tcOutpageCasinoFields.find('#add_tc_outpage_casino_field');

	// Remove item
	function removeItem(e) {
		e.preventDefault();
		const $rowToRemove = $(this).parent();
		const $nextElements = $rowToRemove.nextAll();

		// Fix indexes elements that follow
		$nextElements.each(function () {
			const idx = $(this).index();
			$(this).attr('data-index', idx - 1);
			$(this)
				.find('input')
				.each(function () {
					const initialName = $(this).attr('name');
					$(this).attr('name', initialName.replace(`[${idx}]`, `[${idx - 1}]`));
				});
		});

		// Remove element
		$rowToRemove.remove();
	}
	$allPostTypes.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$outlinkPostTypes.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$tcOutlinkPostTypes.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$outlinkMetaKeys.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$tcOutlinkMetaKeys.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$outpageCasinoFields.on('click', '.rt_hcms_remove_item', 'click', removeItem);
	$tcOutpageCasinoFields.on('click', '.rt_hcms_remove_item', 'click', removeItem);

	function addNewItem(e) {
		e.preventDefault();
		const $options = $(e.target).parent();
		const $optionsWrapper = $options.find('.rt_hcms_arr_wrapper');
		const $lastRow = $options.find('.row').last();
		const rowName = $lastRow.data('name');
		const lastRowIndex = $lastRow.data('index');
		const newIndexRow = $lastRow ? lastRowIndex + 1 : 0;
		const lastRowHtml = $lastRow
			.html()
			.replaceAll(`${rowName}[${lastRowIndex}]`, `${rowName}[${newIndexRow}]`);

		$optionsWrapper.append(`
			<div class="row" data-index="${newIndexRow}" data-name="${rowName}">
				${lastRowHtml}
			</div>
		`);
	}
	$addNewPostTypeBtn.on('click', addNewItem);
	$addOutlinkPostTypeBtn.on('click', addNewItem);
	$addTcOutlinkPostTypeBtn.on('click', addNewItem);
	$addOutlinkMetaKeyBtn.on('click', addNewItem);
	$addTcOutlinkMetaKeyBtn.on('click', addNewItem);
	$addOutpageCasinoFieldBtn.on('click', addNewItem);
	$addTcOutpageCasinoFieldBtn.on('click', addNewItem);

	// Toggle how to text
	const $howToHandle = $('.rt_hcms_how_to_wrapper .dashicons');
	$howToHandle.on('click', function () {
		$(this).parent().find('.rt_hcms_how_to_text').slideToggle();
	});
})(jQuery);
