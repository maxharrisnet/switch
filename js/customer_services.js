function check_form_user() {

	valid = true;

	var invalid = " ";
	var minLength1 = 3; // Minimum length
	var minLength2 = 4; // Minimum length

	if ($('#userFirstName').val() == "")
    {
            alert ( "Please provide a first name..." );
            valid = false;
			$('#userFirstName').focus();
    }

	else if ($('#userLastName').val() == "")
    {
            alert ( "Please provide a last name..." );
            valid = false;
			$('#userLastName').focus();
    }

	else if ($('#address1').val() == "")
    {
            alert ( "Please provide your street address..." );
            valid = false;
			$('#address1').focus();
    }

	else if ($('#city').val() == "")
    {
            alert ( "Please provide your city of residence..." );
            valid = false;
			$('#city').focus();
    }

	else if ($('#postalzip').val() == "")
    {
            alert ( "Please provide your postal or zip code..." );
            valid = false;
			$('#postalzip').focus();
    }

	else if (($('#primaryPH1').val() == "") || ($('#primaryPH2').val() == "") || ($('#primaryPH3').val() == ""))
    {
            alert ( "Please provide a 10-digit primary phone number..." );
            valid = false;
    }

	else if ($('#primaryPH1').val().length < minLength1)
    {
            alert ( "Please provide a 10-digit primary phone number..." );
            valid = false;
			$('#primaryPH1').focus();
    }

	else if ($('#primaryPH2').val().length < minLength1)
    {
            alert ( "Please provide a 10-digit primary phone number..." );
            valid = false;
			$('#primaryPH2').focus();
    }

	else if ($('#primaryPH3').val().length < minLength2)
    {
            alert ( "Please provide a 10-digit primary phone number..." );
            valid = false;
			$('#primaryPH3').focus();
    }

	else if (($('#password').val() != "") && ($('#password2').val() == "")) {

			alert ( "Please enter the same password in both password fields..." );
            valid = false;
			$('#password2').focus();
    }

	else if (($('#password').val() == "") && ($('#password2').val() != "")) {

			alert ( "Please enter the same password in both password fields..." );
            valid = false;
			$('#password').focus();
    }

	else if (($('#password').val().indexOf(invalid) > -1) || ($('#password2').val().indexOf(invalid) > -1)) {

			alert ( "Please do not use spaces in the password..." );
            valid = false;
			$('#password').focus();
    }

	else if (($('#password').val() != "") && ($('#password2').val() != "")) { // both password fields are filled <-- edit password being sent

		if($('#password').val() != $('#password2').val()) { // passwords don't match!

			alert ( "Please make sure the passwords match..." );
            valid = false;
			$('#password2').focus();

		}

		else if ($('#strength').val() < 0.33) {

				alert ( "Password not strong enough. You can do better! Use special characters, ie: !@#$%^&*()_+{}:, if you want to make it strong without being too long." );
                valid = false;
				$('#password').focus();
        }

    }

	return valid;

}

function addChannel(id) {

	valid = true;
	//var dataString = $('#receiver_form_').serialize();
	var dataString = $('#addrow' + id + ' :input').serialize();

	if($('#channel_num' + id).val() == "") {
		alert("The channel number cannot be blank.");
		valid = false;
		$('#channel_num' + id).focus();
	} else {

		$('#button_addchannel' + id).button("loading");
		//alert(dataString);
		$.ajax({
			url: "cust_add_channel.php",
			type: "POST",
			data: dataString,
			cache: false,
			success: function(response) {

				//alert(response);
				//var ajaxDisplay = $('#services_inc_div');
				var ajaxDisplay = document.getElementById("services_inc_div");
				//$(ajaxDisplay).replaceWith(response);
				ajaxDisplay.innerHTML = response;

				$('.tablerow').on('click', 'a.edit', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
			    	var selectID = $('#select' + id).val();
			    	//alert(selectID);

					$('#dropdown' + id).load('customer_service_stationdropdown.php?id=' + selectID , function() {
						// stuff here after load confirmed
						$('#progress_spinner' + id).hide();
					});

			    	$('#viewrow' + id).hide();
			    	$('#editrow' + id).show();
				});

				$('.tablerow').on('click', 'a.cancel', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
			    	$('#viewrow' + id).show();
			    	$('#editrow' + id).hide();
				});

				$('.tablerow').on('click', 'a.delete', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
				    var agree = confirm('Are you sure you want to delete this channel?');
				    if(agree) {
			    		deleteChannel(id);
					} else {
						return false;
					}
				});

				$('.tablerow').on('click', 'a.save', function() {
			    	event.preventDefault();
			    	var id = $(this).data('id');
			    	editChannel(id);
				});

			}
		});

	}


}

function editChannel(id) {

	valid = true;
	//var dataString = $('#receiver_form_').serialize();
	var dataString = $('#editrow' + id + ' :input').serialize();

	if($('#channel_num' + id).val() == "") {
		alert("The channel number cannot be blank.");
		valid = false;
		$('#channel_num' + id).focus();
	} else {

		$('#save_channel' + id).button("loading");
		//alert(dataString);
		$.ajax({
			url: "cust_edit_channel.php",
			type: "POST",
			data: dataString,
			cache: false,
			success: function(response) {

				//alert(response);
				//var ajaxDisplay = $('#services_inc_div');
				var ajaxDisplay = document.getElementById("services_inc_div");
				//$(ajaxDisplay).replaceWith(response);
				ajaxDisplay.innerHTML = response;

				$('.tablerow').on('click', 'a.edit', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
				var siteID = $(this).data('siteID');
			    	var selectID = $('#select' + id).val();
			    	//alert(selectID);

					$('#dropdown' + id).load('customer_service_stationdropdown.php?id=' + selectID , function() {
						// stuff here after load confirmed
						$('#progress_spinner' + id).hide();
					});

			    	$('#viewrow' + id).hide();
			    	$('#editrow' + id).show();
				});

				$('.tablerow').on('click', 'a.cancel', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
			    	$('#viewrow' + id).show();
			    	$('#editrow' + id).hide();
				});

				$('.tablerow').on('click', 'a.delete', function() {
			  		event.preventDefault();
			    	var id = $(this).data('id');
				    var agree = confirm('Are you sure you want to delete this channel?');
				    if(agree) {
			    		deleteChannel(id);
					} else {
						return false;
					}
				});

				$('.tablerow').on('click', 'a.save', function() {
			    	event.preventDefault();
			    	var id = $(this).data('id');
			    	editChannel(id);
				});

			}
		});

	}

}

function deleteChannel(id) {

	valid = true;

	var custID = $('#custID').val();
	var dataString = $('#delete' + id + ' :input').serialize()
	//var dataString = "id=" + id + "&custID=" + custID + "&delete=channel";

	$('#delete_channel' + id).button("loading");
	//alert(dataString);
	$.ajax({
		url: "cust_delete_channel.php",
		type: "POST",
		data: dataString,
		cache: false,
		success: function(response) {

			//alert(response);
			//var ajaxDisplay = $('#services_inc_div');
			var ajaxDisplay = document.getElementById("services_inc_div");
			//$(ajaxDisplay).replaceWith(response);
			ajaxDisplay.innerHTML = response;

			$('.tablerow').on('click', 'a.edit', function() {
		  		event.preventDefault();
		    	var id = $(this).data('id');
		    	var selectID = $('#select' + id).val();
		    	//alert(selectID);

				$('#dropdown' + id).load('customer_service_stationdropdown.php?id=' + selectID , function() {
					// stuff here after load confirmed
					$('#progress_spinner' + id).hide();
				});

		    	$('#viewrow' + id).hide();
		    	$('#editrow' + id).show();
			});

			$('.tablerow').on('click', 'a.cancel', function() {
		  		event.preventDefault();
		    	var id = $(this).data('id');
		    	$('#viewrow' + id).show();
		    	$('#editrow' + id).hide();
			});

			$('.tablerow').on('click', 'a.delete', function() {
		  		event.preventDefault();
		    	var id = $(this).data('id');
			    var agree = confirm('Are you sure you want to delete this channel?');
			    if(agree) {
		    		deleteChannel(id);
				} else {
					return false;
				}
			});

			$('.tablerow').on('click', 'a.save', function() {
		    	event.preventDefault();
		    	var id = $(this).data('id');
		    	editChannel(id);
			});

		}
	});

}
