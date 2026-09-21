//<--------- Start Payment -------//>
(function ($) {
	"use strict";

//<---------------- Add Funds  ----------->>>>
$(document).on('click', '#addFundsBtn', function (s) {

	var isValid = this.form.checkValidity();

	if (isValid) {
		
	s.preventDefault();
	var element = $(this);
	var form = $(this).attr('data-form');
	element.attr({ 'disabled': 'true' });
	var payment = $('input[name=payment_gateway]:checked').val();
	element.find('i').addClass('spinner-border spinner-border-sm align-middle me-1');

	(function () {
		$('#formAddFunds').ajaxForm({
			dataType: 'json',
			success: function (result) {

				// success
				if (result.success && result.instantPayment) {
					window.location.reload();
				}

				if (result.success == true && result.insertBody) {

					$('#bodyContainer').html('');

					$(result.insertBody).appendTo("#bodyContainer");

					if (payment != 1 && payment != 2) {
						element.removeAttr('disabled');
						element.find('i').removeClass('spinner-border spinner-border-sm align-middle me-1');
					}

					$('#errorAddFunds').hide();

				} else if (result.success == true && result.status == 'pending') {
					swal({
						title: thanks,
						text: result.status_info,
						type: "success",
						confirmButtonText: ok
					});

					$('#formAddFunds').trigger("reset");
					element.removeAttr('disabled');
					element.find('i').removeClass('spinner-border spinner-border-sm align-middle me-1');
					$('#previewImage').html('');
					$('#handlingFee, #total, #total2').html('0');
					$('#bankTransferBox').hide();

				} else if (result.success == true && result.url) {
					window.location.href = result.url;
				} else {

					if (result.errors) {

						var error = '';
						var $key = '';

						for ($key in result.errors) {
							error += '<li><i class="far fa-times-circle"></i> ' + result.errors[$key] + '</li>';
						}

						$('#showErrorsFunds').html(error);
						$('#errorAddFunds').show();
						element.removeAttr('disabled');
						element.find('i').removeClass('spinner-border spinner-border-sm align-middle me-1');
					}
				}

			},
			error: function (responseText, statusText, xhr, $form) {
				// error
				element.removeAttr('disabled');
				element.find('i').removeClass('spinner-border spinner-border-sm align-middle me-1');
				swal({
					type: 'error',
					title: error_oops,
					text: error + ' (' + xhr + ')',
				});
			}
		}).submit();
	})(); //<--- FUNCTION %
	}
});//<<<-------- * END FUNCTION CLICK * ---->>>>
//============ End Payment =================//

$('input[name=payment_gateway]').on('click', function() {
	if ($(this).hasClass('bankTriggerClass')) {
		$('#bankTransferBox').slideDown();
	} else {
		$('#bankTransferBox').slideUp();
	}
});

$(document).on('click','.removeFile',function() {
	$('#previewImage').html('');
	$('#fileBankTransfer').val('');
});

//======= FILE Bank Transfer
$("#fileBankTransfer").on('change', function() {
	$('#previewImage').html('');
   
	var loaded = false;
	if(window.File && window.FileReader && window.FileList && window.Blob) {
			//check empty input filed
		if($(this).val()) {
			   var oFReader = new FileReader(), rFilter = /^(?:image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/png|image)$/i;
			if($(this)[0].files.length === 0){return}
   
			var oFile = $(this)[0].files[0];
			var fsize = $(this)[0].files[0].size; //get file size
			var ftype = $(this)[0].files[0].type; // get file type
   
			   if(!rFilter.test(oFile.type)) {
				$('#fileBankTransfer').val('');
				   swal({
					title: error,
					text: formats_available,
					type: "error",
					confirmButtonText: ok
					});
				return false;
			}
   
			var allowed_file_size = 1048576;
   
			if(fsize>allowed_file_size){
				$('.popout').addClass('popout-error').html(max_size_upload).fadeIn(500).delay(4000).fadeOut();
				   $(this).val('');
				return false;
			}
			$('#previewImage').html('<strong>' + oFile.name + '</strong> <i class="bi-trash-fill ms-1 removeFile c-pointer text-danger"></i>');
   
		}
	} else {
		alert('Can\'t upload! Your browser does not support File API! Try again with modern browsers like Chrome or Firefox.');
		return false;
	}
   });
   //======= END FILE Bank Transfer

})(jQuery);
