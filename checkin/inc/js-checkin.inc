<script language="javascript" type="text/javascript">
<!--

$(function() {

	$('#search-input').on('keypress', function(e) {
		if (e.which == 13) {
			var $btn = $('#search-btn');
			showWait($btn);
			sendReq('../checkin/inc/ajax.php', ['action', 'query'], ['search', $('#search-input').val()], do_element);
			return false;
		}
	});
	$('#search-btn').on('click', function() {
		var $btn = $('#search-btn');
		showWait($btn);
		sendReq('../checkin/inc/ajax.php', ['action', 'query'], ['search', $('#search-input').val()], do_element);
	});

	$('body').on('click', '.checkin-search-result', function() {
		if ($(this).hasClass('checked-in')) {
			//Checkout
			if($('body').hasClass('admin') && confirm($(this).data('checkout-confirm'))) {
				sendReq('../checkin/inc/ajax.php', ['action', 'id'], ['checkout',$(this).data('person-id')], do_element);
			} else {
				return false;
			}

		} else {
			//Checkin
			if($('.checkin-selected-btn').length == 0) {
				sendReq('../checkin/inc/ajax.php', ['action', 'ids'], ['checkin', $(this).data('person-id')], do_element);
			}
		}

		var isActive = $(this).hasClass('active');
		if (isActive) {
			$(this).removeClass('active').find('.checked-indicator').removeClass('fa-check-square-o').addClass('fa-square-o');
		} else {
			$(this).addClass('active').find('.checked-indicator').removeClass('fa-square-o').addClass('fa-check-square-o');
		}


		if ($('.checkin-search-result.active').length == 0) {
			$('.checkin-selected-btn').prop('disabled', true);
		} else {
			$('.checkin-selected-btn').prop('disabled', false);
		}
	});

	$('body').on('click', '.checkin-selected-btn', function() {
		showWait($(this));
		var selected = [];
		$('.checkin-search-result.active').each(function(index) {
			selected.push($(this).data('person-id'));
		});
		sendReq('../checkin/inc/ajax.php', ['action', 'ids'], ['checkin', selected.join()], do_element);
	});

	$('#add-person-btn').asyncform({tag: 'checkin_add_person'});
	$('#add-person-btn').on('asyncform.response', function(event, r) {
		var person = r.actions.ko_leute.insert.entries[r.actions.ko_leute.insert.ids[0]];
		var descParts = [];
		if (person.vorname) descParts.push(person.vorname);
		if (person.nachname) descParts.push(person.nachname);
		$('#search-input').val(descParts.join(' '));
		$('#search-btn').click();
		$('#search-input').focus();
	});
});


function showWait($el) {
	if (!$el.data('is-waiting')) {
		$el.data('is-waiting', true);
		$el.data('former-content', $el.html());
		$el.data('former-disabled', $el.prop('disabled'));
		$el.width($el.width());
		$el.prop('disabled', true);
		$el.html('<i class="fa fa-spin fa-spinner icon-line-height"></i>');
	}
}


function hideWait($el) {
	if ($el.data('is-waiting')) {
		$el.html($el.data('former-content'));
		$el.prop('disabled', $el.data('former-disabled'));
		$el.data('is-waiting', false);
	}
}

-->
</script>
