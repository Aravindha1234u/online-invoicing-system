<?php

// Data functions (insert, update, delete, form) for table invoice_items

// This script and data application were generated by AppGini 5.81
// Download AppGini for free from https://bigprof.com/appgini/download/

function invoice_items_insert() {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('invoice_items');
	if(!$arrPerm[1]) return false;

	$data = array();
	$data['item'] = $_REQUEST['item'];
		if($data['item'] == empty_lookup_value) { $data['item'] = ''; }
	$data['unit_price'] = $_REQUEST['unit_price'];
		if($data['unit_price'] == empty_lookup_value) { $data['unit_price'] = ''; }
	$data['qty'] = $_REQUEST['qty'];
		if($data['qty'] == empty_lookup_value) { $data['qty'] = ''; }
	if($data['unit_price']== '') {
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">" . $Translation['error:'] . " 'Unit price': " . $Translation['field not null'] . '<br><br>';
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}
	if($data['qty'] == '') $data['qty'] = "1";

	// hook: invoice_items_before_insert
	if(function_exists('invoice_items_before_insert')) {
		$args = array();
		if(!invoice_items_before_insert($data, getMemberInfo(), $args)) { return false; }
	}

	$error = '';
	// set empty fields to NULL
	$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
	insert('invoice_items', backtick_keys_once($data), $error);
	if($error)
		die("{$error}<br><a href=\"#\" onclick=\"history.go(-1);\">{$Translation['< back']}</a>");

	$recID = db_insert_id(db_link());

	// automatic invoice if passed as filterer
	if($_REQUEST['filterer_invoice']) {
		sql("update `invoice_items` set `invoice`='" . makeSafe($_REQUEST['filterer_invoice']) . "' where `id`='" . makeSafe($recID, false) . "'", $eo);
	}

	// hook: invoice_items_after_insert
	if(function_exists('invoice_items_after_insert')) {
		$res = sql("select * from `invoice_items` where `id`='" . makeSafe($recID, false) . "' limit 1", $eo);
		if($row = db_fetch_assoc($res)) {
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args=array();
		if(!invoice_items_after_insert($data, getMemberInfo(), $args)) { return $recID; }
	}

	// mm: save ownership data
	set_record_owner('invoice_items', $recID, getLoggedMemberID());

	// if this record is a copy of another record, copy children if applicable
	if(!empty($_REQUEST['SelectedID'])) invoice_items_copy_children($recID, $_REQUEST['SelectedID']);

	return $recID;
}

function invoice_items_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = array(); // array of curl handlers for launching insert requests
	$eo = array('silentErrors' => true);
	$uploads_dir = realpath(dirname(__FILE__) . '/../' . $Translation['ImageFolder']);
	$safe_sid = makeSafe($source_id);

	// launch requests, asynchronously
	curl_batch($requests);
}

function invoice_items_delete($selected_id, $AllowDeleteOfParents=false, $skipChecks=false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id=makeSafe($selected_id);

	// mm: can member delete record?
	$arrPerm=getTablePermissions('invoice_items');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='invoice_items' and pkValue='$selected_id'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='invoice_items' and pkValue='$selected_id'");
	if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3) { // allow delete?
		// delete allowed, so continue ...
	}else{
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: invoice_items_before_delete
	if(function_exists('invoice_items_before_delete')) {
		$args=array();
		if(!invoice_items_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'];
	}

	sql("delete from `invoice_items` where `id`='$selected_id'", $eo);

	// hook: invoice_items_after_delete
	if(function_exists('invoice_items_after_delete')) {
		$args=array();
		invoice_items_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("delete from membership_userrecords where tableName='invoice_items' and pkValue='$selected_id'", $eo);
}

function invoice_items_update($selected_id) {
	global $Translation;

	// mm: can member edit record?
	$arrPerm=getTablePermissions('invoice_items');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='invoice_items' and pkValue='".makeSafe($selected_id)."'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='invoice_items' and pkValue='".makeSafe($selected_id)."'");
	if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3) { // allow update?
		// update allowed, so continue ...
	}else{
		return false;
	}

	$data['item'] = makeSafe($_REQUEST['item']);
		if($data['item'] == empty_lookup_value) { $data['item'] = ''; }
	$data['unit_price'] = makeSafe($_REQUEST['unit_price']);
		if($data['unit_price'] == empty_lookup_value) { $data['unit_price'] = ''; }
	if($data['unit_price']=='') {
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">{$Translation['error:']} 'Unit price': {$Translation['field not null']}<br><br>";
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}
	$data['qty'] = makeSafe($_REQUEST['qty']);
		if($data['qty'] == empty_lookup_value) { $data['qty'] = ''; }
	$data['selectedID'] = makeSafe($selected_id);

	// hook: invoice_items_before_update
	if(function_exists('invoice_items_before_update')) {
		$args = array();
		if(!invoice_items_before_update($data, getMemberInfo(), $args)) { return false; }
	}

	$o = array('silentErrors' => true);
	sql('update `invoice_items` set       `item`=' . (($data['item'] !== '' && $data['item'] !== NULL) ? "'{$data['item']}'" : 'NULL') . ', `unit_price`=' . (($data['unit_price'] !== '' && $data['unit_price'] !== NULL) ? "'{$data['unit_price']}'" : 'NULL') . ', `qty`=' . (($data['qty'] !== '' && $data['qty'] !== NULL) ? "'{$data['qty']}'" : 'NULL') . " where `id`='".makeSafe($selected_id)."'", $o);
	if($o['error']!='') {
		echo $o['error'];
		echo '<a href="invoice_items_view.php?SelectedID='.urlencode($selected_id)."\">{$Translation['< back']}</a>";
		exit;
	}


	// hook: invoice_items_after_update
	if(function_exists('invoice_items_after_update')) {
		$res = sql("SELECT * FROM `invoice_items` WHERE `id`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) {
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = $data['id'];
		$args = array();
		if(!invoice_items_after_update($data, getMemberInfo(), $args)) { return; }
	}

	// mm: update ownership data
	sql("update `membership_userrecords` set `dateUpdated`='" . time() . "' where `tableName`='invoice_items' and `pkValue`='" . makeSafe($selected_id) . "'", $eo);

}

function invoice_items_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0, $TemplateDV = '', $TemplateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm=getTablePermissions('invoice_items');
	if(!$arrPerm[1] && $selected_id=='') { return ''; }
	$AllowInsert = ($arrPerm[1] ? true : false);
	// print preview?
	$dvprint = false;
	if($selected_id && $_REQUEST['dvprint_x'] != '') {
		$dvprint = true;
	}

	$filterer_invoice = thisOr(undo_magic_quotes($_REQUEST['filterer_invoice']), '');
	$filterer_item = thisOr(undo_magic_quotes($_REQUEST['filterer_item']), '');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: invoice
	$combo_invoice = new DataCombo;
	// combobox: item
	$combo_item = new DataCombo;

	if($selected_id) {
		// mm: check member permissions
		if(!$arrPerm[2]) {
			return "";
		}
		// mm: who is the owner?
		$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='invoice_items' and pkValue='".makeSafe($selected_id)."'");
		$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='invoice_items' and pkValue='".makeSafe($selected_id)."'");
		if($arrPerm[2]==1 && getLoggedMemberID()!=$ownerMemberID) {
			return "";
		}
		if($arrPerm[2]==2 && getLoggedGroupID()!=$ownerGroupID) {
			return "";
		}

		// can edit?
		if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3) {
			$AllowUpdate=1;
		}else{
			$AllowUpdate=0;
		}

		$res = sql("SELECT * FROM `invoice_items` WHERE `id`='" . makeSafe($selected_id) . "'", $eo);
		if(!($row = db_fetch_array($res))) {
			return error_message($Translation['No records found'], 'invoice_items_view.php', false);
		}
		$combo_invoice->SelectedData = $row['invoice'];
		$combo_item->SelectedData = $row['item'];
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input();
		$row = $hc->xss_clean($row); /* sanitize data */
	} else {
		$combo_invoice->SelectedData = $filterer_invoice;
		$combo_item->SelectedData = $filterer_item;
	}
	$combo_invoice->HTML = '<span id="invoice-container' . $rnd1 . '"></span><input type="hidden" name="invoice" id="invoice' . $rnd1 . '" value="' . html_attr($combo_invoice->SelectedData) . '">';
	$combo_invoice->MatchText = '<span id="invoice-container-readonly' . $rnd1 . '"></span><input type="hidden" name="invoice" id="invoice' . $rnd1 . '" value="' . html_attr($combo_invoice->SelectedData) . '">';
	$combo_item->HTML = '<span id="item-container' . $rnd1 . '"></span><input type="hidden" name="item" id="item' . $rnd1 . '" value="' . html_attr($combo_item->SelectedData) . '">';
	$combo_item->MatchText = '<span id="item-container-readonly' . $rnd1 . '"></span><input type="hidden" name="item" id="item' . $rnd1 . '" value="' . html_attr($combo_item->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_invoice__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['invoice'] : $filterer_invoice); ?>"};
		AppGini.current_item__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['item'] : $filterer_item); ?>"};

		jQuery(function() {
			setTimeout(function() {
				if(typeof(invoice_reload__RAND__) == 'function') invoice_reload__RAND__();
				if(typeof(item_reload__RAND__) == 'function') item_reload__RAND__();
			}, 10); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function invoice_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#invoice-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_invoice__RAND__.value, t: 'invoice_items', f: 'invoice' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="invoice"]').val(resp.results[0].id);
							$j('[id=invoice-container-readonly__RAND__]').html('<span id="invoice-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=invoices_view_parent]').hide(); }else{ $j('.btn[id=invoices_view_parent]').show(); }


							if(typeof(invoice_update_autofills__RAND__) == 'function') invoice_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { /* */ return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { /* */ return { s: term, p: page, t: 'invoice_items', f: 'invoice' }; },
					results: function(resp, page) { /* */ return resp; }
				},
				escapeMarkup: function(str) { /* */ return str; }
			}).on('change', function(e) {
				AppGini.current_invoice__RAND__.value = e.added.id;
				AppGini.current_invoice__RAND__.text = e.added.text;
				$j('[name="invoice"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=invoices_view_parent]').hide(); }else{ $j('.btn[id=invoices_view_parent]').show(); }


				if(typeof(invoice_update_autofills__RAND__) == 'function') invoice_update_autofills__RAND__();
			});

			if(!$j("#invoice-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_invoice__RAND__.value, t: 'invoice_items', f: 'invoice' },
					success: function(resp) {
						$j('[name="invoice"]').val(resp.results[0].id);
						$j('[id=invoice-container-readonly__RAND__]').html('<span id="invoice-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=invoices_view_parent]').hide(); }else{ $j('.btn[id=invoices_view_parent]').show(); }

						if(typeof(invoice_update_autofills__RAND__) == 'function') invoice_update_autofills__RAND__();
					}
				});
			}

		<?php }else{ ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_invoice__RAND__.value, t: 'invoice_items', f: 'invoice' },
				success: function(resp) {
					$j('[id=invoice-container__RAND__], [id=invoice-container-readonly__RAND__]').html('<span id="invoice-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=invoices_view_parent]').hide(); }else{ $j('.btn[id=invoices_view_parent]').show(); }

					if(typeof(invoice_update_autofills__RAND__) == 'function') invoice_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
		function item_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#item-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_item__RAND__.value, t: 'invoice_items', f: 'item' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="item"]').val(resp.results[0].id);
							$j('[id=item-container-readonly__RAND__]').html('<span id="item-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); }else{ $j('.btn[id=items_view_parent]').show(); }


							if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { /* */ return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { /* */ return { s: term, p: page, t: 'invoice_items', f: 'item' }; },
					results: function(resp, page) { /* */ return resp; }
				},
				escapeMarkup: function(str) { /* */ return str; }
			}).on('change', function(e) {
				AppGini.current_item__RAND__.value = e.added.id;
				AppGini.current_item__RAND__.text = e.added.text;
				$j('[name="item"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); }else{ $j('.btn[id=items_view_parent]').show(); }


				if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
			});

			if(!$j("#item-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_item__RAND__.value, t: 'invoice_items', f: 'item' },
					success: function(resp) {
						$j('[name="item"]').val(resp.results[0].id);
						$j('[id=item-container-readonly__RAND__]').html('<span id="item-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); }else{ $j('.btn[id=items_view_parent]').show(); }

						if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
					}
				});
			}

		<?php }else{ ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_item__RAND__.value, t: 'invoice_items', f: 'item' },
				success: function(resp) {
					$j('[id=item-container__RAND__], [id=item-container-readonly__RAND__]').html('<span id="item-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); }else{ $j('.btn[id=items_view_parent]').show(); }

					if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_contents());
	ob_end_clean();


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/invoice_items_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	}else{
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/invoice_items_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Invoice item details', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($AllowInsert) {
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return invoice_items_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return invoice_items_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if($_REQUEST['Embedded']) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	}else{
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id) {
		if(!$_REQUEST['Embedded']) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$j(\'form\').eq(0).prop(\'novalidate\', true); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate) {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return invoice_items_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3) { // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate && !$AllowInsert) || (!$selected_id && !$AllowInsert)) {
		$jsReadOnly .= "\tjQuery('#item').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#item_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#unit_price').replaceWith('<div class=\"form-control-static\" id=\"unit_price\">' + (jQuery('#unit_price').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#qty').replaceWith('<div class=\"form-control-static\" id=\"qty\">' + (jQuery('#qty').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	}elseif($AllowInsert) {
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
			$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(invoice)%%>', $combo_invoice->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(invoice)%%>', $combo_invoice->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(invoice)%%>', urlencode($combo_invoice->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(item)%%>', $combo_item->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(item)%%>', $combo_item->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(item)%%>', urlencode($combo_item->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
	$lookup_fields = array('invoice' => array('invoices', 'Invoice'), 'item' => array('items', 'Item'), );
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-md" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] && !$_REQUEST['Embedded']) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent hspacer-md" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(invoice)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(item)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(catalog_price)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(unit_price)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(qty)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(price)%%>', '', $templateCode);

	// process values
	if($selected_id) {
		$templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		$templateCode = str_replace('<%%VALUE(invoice)%%>', safe_html($urow['invoice']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(invoice)%%>', urlencode($urow['invoice']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(item)%%>', safe_html($urow['item']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(item)%%>', html_attr($row['item']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(item)%%>', urlencode($urow['item']), $templateCode);
		$templateCode = str_replace('<%%VALUE(catalog_price)%%>', safe_html($urow['catalog_price']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(catalog_price)%%>', urlencode($urow['catalog_price']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(unit_price)%%>', safe_html($urow['unit_price']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(unit_price)%%>', html_attr($row['unit_price']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(unit_price)%%>', urlencode($urow['unit_price']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(qty)%%>', safe_html($urow['qty']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(qty)%%>', html_attr($row['qty']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(qty)%%>', urlencode($urow['qty']), $templateCode);
		$templateCode = str_replace('<%%VALUE(price)%%>', safe_html($urow['price']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(price)%%>', urlencode($urow['price']), $templateCode);
	}else{
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(invoice)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(invoice)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(item)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(item)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(catalog_price)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(catalog_price)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(unit_price)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(unit_price)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(qty)%%>', '1', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(qty)%%>', urlencode('1'), $templateCode);
		$templateCode = str_replace('<%%VALUE(price)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(price)%%>', urlencode(''), $templateCode);
	}

	// process translations
	foreach($Translation as $symbol=>$trans) {
		$templateCode = str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
	}

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if($_REQUEST['dvprint_x'] == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id) {
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields
	if( $_REQUEST['FilterField'][1]=='2' && $_REQUEST['FilterOperator'][1]=='<=>') {
		$templateCode.="\n<input type=hidden name=invoice value=\"" . html_attr((get_magic_quotes_gpc() ? stripslashes($_REQUEST['FilterValue'][1]) : $_REQUEST['FilterValue'][1]))."\">\n";
	}

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('invoice_items');
	if($selected_id) {
		$jdata = get_joined_record('invoice_items', $selected_id);
		if($jdata === false) $jdata = get_defaults('invoice_items');
		$rdata = $row;
	}
	$templateCode .= loadView('invoice_items-ajax-cache', array('rdata' => $rdata, 'jdata' => $jdata));

	// hook: invoice_items_dv
	if(function_exists('invoice_items_dv')) {
		$args=array();
		invoice_items_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}
?>