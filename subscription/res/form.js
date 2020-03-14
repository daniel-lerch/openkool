 
$(document).ready(function() {
	$('input.datetimepicker').datetimepicker({
		format:'DD.MM.YYYY',
		locale:$('html').attr('lang'),
	});
	$('form.kOOLSubscriptionForm').each(function() {
		var form = this;

		var toggleDatafields = function() {
			var groups = new Set();
			$('.lpcGroupSelect',form).each(function() {
				var g = $(this).val();
				if(g) {
					groups.add(g);
				}
			});
			$('.groupInput:checked',form).map(function(i,e) {
				groups.add($(e).data('group'));
			});
			$('.datafieldInput',form).each(function() {
				var show = groups.has($(this).data('group'));
				var formGroup = $(this).closest('.lpcFormGroup');
				formGroup.toggle(show);
				$(this).prop('required',show && formGroup.hasClass('mandatory'));
			});
		};

		var groupSelectChange = function() {
			var select = $('.lpcGroupSelect',form);
			var formGroup = select.closest('.lpcFormGroup');
			if(select.is('select')) {
				var data = select.find('option:selected').data();
				formGroup.find('.lpcFormError').filter('.error,.warning').remove();
				if(data.warning || data.error) {
					formGroup.append($('<div>').addClass('lpcFormError').addClass(data.error ? 'error' : 'warning').append($('<div>').text(data.error ? data.error : data.warning)));
				} else {
					formGroup.find('.lpcFormError.warning').remove();
				}
				if(data.error) {
					formGroup.addClass('error');
					select[0].setCustomValidity(data.error);
				} else {
					formGroup.toggleClass('error',formGroup.find('.lpcFormError').not('.warning').length > 0);
					select[0].setCustomValidity('');
				}
			}
			toggleDatafields();
		};

		groupSelectChange();
		$('.groupInput, .lpcGroupSelect',form).change(groupSelectChange);

		$(form).submit(function() {
			if($(this).hasClass('busy')) {
				return false;
			}
			$(this).addClass('busy');
			$('.submitButton',this).prop('disabled',true);
		});
	});

});
