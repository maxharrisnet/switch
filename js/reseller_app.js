function check_reseller_app() {

	valid = true;

	if($("#firstname").val() == "") {
		alert("Please enter your first name.");
		valid = false;
		$("#firstname").focus();
	} else if($("#lastname").val() == "") {
		alert("Please enter your last name.");
		valid = false;
		$("#lastname").focus();
	} else if($("#title").val() == "") {
		alert("Please enter your title or position.");
		valid = false;
		$("#title").focus();
	} else if($("#email").val() == "") {
		alert("Please enter your email address.");
		valid = false;
		$("#email").focus();
	} else if($("#phone").val() == "") {
		alert("Please enter your primary phone number.");
		valid = false;
		$("#phone").focus();
	} else if($("#company").val() == "") {
		alert("Please enter your company name (or indicate if you are a sole proprietor).");
		valid = false;
		$("#company").focus();
	} else if($("#website").val() == "") {
		alert("Please enter your website address.");
		valid = false;
		$("#website").focus();
	} else if($("#street").val() == "") {
		alert("Please enter your street address.");
		valid = false;
		$("#street").focus();
	} else if($("#city").val() == "") {
		alert("Please enter your city.");
		valid = false;
		$("#city").focus();
	} else if($("#province").val() == "") {
		alert("Please enter your province.");
		valid = false;
		$("#province").focus();
	} else if($("#countryID").val() == "") {
		alert("Please enter your country.");
		valid = false;
		$("#countryID").focus();
	} else if($("#postal_code").val() == "") {
		alert("Please enter your postal code.");
		valid = false;
		$("#postal_code").focus();
	} else if($("#where_heard_about_Switch").val() == "") {
		alert("Please tell us where you heard about Switch.");
		valid = false;
		$("#where_heard_about_Switch").focus();
	} else if($("#sales_coverage").val() == "") {
		alert("Please indicate your sales coverage using the options provided.");
		valid = false;
		$("#sales_coverage").focus();
	} else if($("#locations_branches").val() == "") {
		alert("Please tell us how many locations or branches you have.");
		valid = false;
		$("#locations_branches").focus();
	} else if($("#territories").val() == "") {
		alert("Please explain your coverage model (metro areas, regions, etc...)");
		valid = false;
		$("#territories").focus();
	} else if($("#total_revenues").val() == "") {
		alert("Please provide your total annual revenues.");
		valid = false;
		$("#total_revenues").focus();
	} else if($("#networking_revenue_percentage").val() == "") {
		alert("Please tell us the percentage of your revenue that is a result of  networking.");
		valid = false;
		$("#networking_revenue_percentage").focus();
	} else if($("#overall_headcount").val() == "") {
		alert("Please indicate the overall head count at your company.");
		valid = false;
		$("#overall_headcount").focus();
	} else if($("#sales_headcount").val() == "") {
		alert("Please provide the head count for your sales division/department.");
		valid = false;
		$("#sales_headcount").focus();
	}

	return valid;

}
