{strip}
	<style>
		body {
			background: url(layouts/v7/resources/Images/bg-pattern.png);
			background-position: center;
			background-size: cover;
			width: 100%;
			background-repeat: no-repeat;
			font-family: Rubik, sans-serif;
			color: #6c757d;
		}
		hr {
			margin-top: 15px;
			background-color: #7C7C7C;
			height: 2px;
			border-width: 0;
		}
		h3,
		h4 {
			margin-top: 0px;
		}

		hgroup {
			text-align: center;
			margin-top: 4em;
		}

		input {
			font-size: 16px;
			padding: 10px 10px 10px 0px;
			-webkit-appearance: none;
			display: block;
			color: #636363;
			width: 100%;
			border: none;
			border-radius: 0;
			border-bottom: 1px solid #757575;
		}

		input:focus {
			outline: none;
		}

		label {
			font-weight: normal;
			pointer-events: none;
			left: 0px;
			top: 10px;
			transition: all 0.2s ease;
		}
		input:focus label,
		input.used label {
			top: -20px;
			transform: scale(.75);
			left: -12px;
			font-size: 18px;
		}
		input:focus .bar:before,
		input:focus .bar:after {
			width: 50%;
		}

		#page {
			padding-top: 0px;
		}

		.widgetHeight {
			height: 500px;
			margin-top: 20px !important;
		}

		.loginDiv {
			max-width: 500px;
			margin: 0 auto;
			border-radius: 4px;
			box-shadow: 0 0 10px gray;
			background-color: #FFFFFF;
		}

		.marketingDiv {
			color: #303030;
		}

		.separatorDiv {
			background-color: #7C7C7C;
			width: 2px;
			height: 460px;
			margin-left: 20px;
		}

		.user-logo {
			margin: 0 auto;
		}

		.blockLink {
			border: 1px solid #303030;
			padding: 3px 5px;
		}

		.group {
			position: relative;
			margin: 20px 20px 40px;
		}

		.failureMessage {
			color: red;
			display: block;
			text-align: center;
			padding: 0px 0px 10px;
		}

		.successMessage {
			color: green;
			display: block;
			text-align: center;
			padding: 0px 0px 10px;
		}

		.inActiveImgDiv {
			padding: 5px;
			text-align: center;
			margin: 30px 0px;
		}

		.app-footer p {
			margin-top: 0px;
		}

		.footer {
			background-color: #fbfbfb;
			height: 26px;
		}

		.bar {
			position: relative;
			display: block;
			width: 100%;
		}

		.bar:before,
		.bar:after {
			content: '';
			width: 0;
			bottom: 1px;
			position: absolute;
			height: 1px;
			background: #35aa47;
			transition: all 0.2s ease;
		}

		.bar:before {
			left: 50%;
		}

		.bar:after {
			right: 50%;
		}

		.button {
			position: relative;
			display: inline-block;
			padding: 9px;
			margin: .3em 0 1em 0;
			width: 100%;
			vertical-align: middle;
			color: #fff;
			font-size: 16px;
			line-height: 20px;
			-webkit-font-smoothing: antialiased;
			text-align: center;
			letter-spacing: 1px;
			background: transparent;
			border: 0;
			cursor: pointer;
			transition: all 0.15s ease;
		}

		.button:focus {
			outline: 0;
		}

		.buttonBlue {
			background-color: #5671f0;
		}

		.ripples {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
			background: transparent;
		}

		@keyframes inputHighlighter {
			from {
				background: #4a89dc;
			}

			to {
				width: 0;
				background: transparent;
			}
		}

		@keyframes ripples {
			0% {
				opacity: 0;
			}

			25% {
				opacity: 1;
			}

			100% {
				width: 200%;
				padding-bottom: 200%;
				opacity: 0;
			}
		}

		.bg-pattern {
			background: url(layouts/v7/resources/Images/bg-pattern-2.png);
			background-size: cover;
		}

		.p-4 {
			padding: 6.25rem 4.25rem !important;
		}

		.mb-4 {
			margin-bottom: 15px !important;
		}

		.mt-3 {
			margin-top: 2.5rem !important
		}

		.auto_text {
			text-align: center;
			width: 75%;
			margin: auto;
		}

		.text-muted {
			color: #98a6ad !important;
		}

		#SignUpFormDiv .login_form .input-group-btn {
			vertical-align: bottom;
		}

		#SignUpFormDiv .submit_btn button {
			font-size: 13px;
			line-height: 20px;
			font-weight: 400;
			letter-spacing: normal;
		}

		#SignUpFormDiv .submit_btn .btn-primary:hover {
			border: none;
		}

		#SignUpFormDiv input {
			border-top-color: #d9d9d9;
			border-radius: .25rem;
		}

		#SignUpFormDiv input:focus {
			box-shadow: none;
			border-color: #d9d9d9;
		}

		#SignUpFormDiv .input-group input {
			border-radius: 0.25rem 0rem 0rem 0.25rem !important;
		}
		#SignUpFormDiv .login_form .input-group-btn .btn-eye svg {
			width: 15px;
			vertical-align: middle;
		}
		#SignUpFormDiv .input-group .input-group-btn .btn-eye {
			border-radius: 0rem 0.25rem 0.25rem 0rem !important;
			padding: 2px 10px;
		}

		.text-white-50 {
			color: rgba(255, 255, 255, .5) !important;
		}

		.forgot_pwd {
			text-align: center;
			margin-top: 20px;
		}

		.copy_right {
			width: 100%;
			bottom: 15px;
			position: absolute;
			text-align: center;
		}

		#SignUpFormDiv .form-group {
			margin-bottom: 20px;
		}
		.carousel{
			background-color: #fff;
			background-image: none;
		}
		.carousel-inner .item img {
			width: 100%;
			height: 100%;
		}
		.ui-datepicker table {
			background-color: blanchedalmond;
		}
		.modal-header .close {
			margin-top: 0px !important;
		}
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
		}

		/* Firefox */
		input[type=number] {
		-moz-appearance: textfield;
		}
	</style>
	<span class="app-nav"></span>
	<div class="container-fluid loginPageContainer">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login_title">
			<h1 style="margin: 0px" class="text-center">Welcome To BEML CCHS Portal</h1>
		</div>
		<input type="hidden" name="OFFICETOROLEDEPENDENCY" data-value='{ZEND_JSON::encode($OFFICETOROLEDEPENDENCY)|escape}' />
		<input id="uidsaver" type="text" hidden>
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 login_beml">
			<form class="form-horizontal" id="SignUpFormDiv" name="SignUpFormDiv">
				<div class="loginDiv widgetHeight ">
					<div class="bg-pattern">
						<div style="padding-top: 10px">
							<p class="text-muted mb-4 mt-3 auto_text">Welcome to Customer Complaint Handling Solutions Portal </p>
							<div>
								<span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
								<span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
							</div>
							<input id="record" type="hidden" class="form-control"  name="record" value="{$PRERESETRECORDID}">
							<span id="user_password">
								<label for="user_password" class="col-sm-4 control-label"> Password <span class="text-danger">*</span></label>
								<div class="col-sm-8 form-group">
									<input id="password_1"  data-rule-required="true" {literal}data-specific-rules='[{"name":"passwordmix"},{"name" : "maxsizeInSignUP", "params":"18"},{"name" : "allowOnlyCertainSpecialCharcters"}]'{/literal} type="password" class="form-control"  name="user_password" autofill="autofill">
									<span id="togglePassword1" style="margin-top: -25px ;margin-right:10px;float: right;cursor: pointer;" class="glyphicon glyphicon-eye-open"></span> 
								</div>
							</span>
							<span id="confirm_password">
								<label for="confirm_password" class="col-sm-4 control-label">Confirm Password <span class="text-danger">*</span></label>
								<div class="col-sm-8 form-group">
									<input id="password_2"  data-rule-required="true" {literal}data-specific-rules='[{"name":"passwordmix"},{"name" : "maxsizeInSignUP", "params":"18"},{"name" : "checkPasswordMatching"},{"name" : "allowOnlyCertainSpecialCharcters"}]'{/literal} type="password" class="form-control"  name="confirm_password" autofill="autofill">
									<span id="togglePassword2" style="margin-top: -25px ;margin-right:10px;float: right;cursor: pointer;" class="glyphicon glyphicon-eye-open"></span> 
								</div>
							</span>
						</div>
					</div>
					<div class="submit_btn form-group" style="padding : 20px;padding-top : 0px">
						<div class="col-sm-12">
							<button id="SignUPSubmitButton" class="button buttonBlue crm_btn btn btn-primary">Reset Password</button><br>
							<br>
						</div>
					</div>
				</div>
			</form>
			<div class="forgot_pwd">
				<a style="display:none" href="forgotPasswordDiv" class="forgotPasswordLink text-white-50" style="color: #15c;">Forgot password?</a>
			</div>
		</div>
	</div>
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">OTP</h4>
				</div>
				<div class="modal-body">
				<form class="form-horizontal" id="SignUpFormDiv1" name="signupform1">
					<input class="form-control" id="otp" type="text">
					<div>
						<button id="OTPSubmit" class="button buttonBlue crm_btn btn btn-primary">Submit</button><br>
					</div>
					<div id="time">
						<span>Resend OTP In </span><div id="output">00:00</div>
					</div>
					<div id="resendOtpDiv" style="display:none">
						<button id="resendOtp" class="button buttonBlue crm_btn btn btn-primary">Resend OTP</button><br>
					</div>
				</form>
				</div>
				<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
  	</div>
	<div class="modal fade" id="ShowSucess" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Password Is Changed Succesfuly</h4>
				</div>
				<div class="modal-body">
					<p>Password Is Changed Succesfuly </p>
				</div>
				<div class="modal-footer">
				<button type="button" id="Redidirect" class="btn btn-default" data-dismiss="modal">Ok</button>
				</div>
			</div>
		</div>
  	</div>
	<div class="modal fade" id="ShowError" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Error</h4>
				</div>
				<div id="errorModalContent" class="modal-body">
					
				</div>
				<div class="modal-footer">
				<button type="button" id="Redidirect" class="btn btn-default" data-dismiss="modal">Ok</button>
				</div>
			</div>
		</div>
  	</div>
	<script type="text/javascript" src="{vresource_url('layouts/v7/resources/v7_client_compat.js')}"></script>
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Users/resources/intlTelInput.js')}"></script>
	</div>
{/strip}