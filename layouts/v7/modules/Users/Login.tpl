{*+********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Users/views/Login.php *}

{strip}
	<style>
		body {
			background: url(layouts/v7/resources/Images/bg-pattern.png);
			/*background-color: #dcd5d7;*/
			/*background-color: #4a81d4;*/
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
			/*font-size: 16px;*/
			font-weight: normal;
			/*position: absolute;*/
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
			padding-top: 85px;
		}

		.widgetHeight {
			/*height: 360px;*/
			height: 500px;
			margin-top: 20px !important;
		}

		.loginDiv {
			max-width: 430px;
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
			/*height: 90px;*/
			margin: 0 auto;
			/*padding-top: 40px;*/
			/*padding-bottom: 20px;*/
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
			/*background-image: linear-gradient(to bottom, #35aa47 0px, #35aa47 100%)*/
			background-color: #5671f0;
			/*border-color: #5671f0;*/
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

		/*Animations*/
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

		/*login_css*/
		.bg-pattern {
			background: url(layouts/v7/resources/Images/bg-pattern.png);
			background-size: cover;
		}

		.p-4 {
			padding: 6.25rem 4.25rem !important;
		}

		.mb-4 {
			margin-bottom: 5.25rem !important;
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

		#loginFormDiv .login_form .input-group-btn {
			vertical-align: bottom;
		}

		#loginFormDiv .submit_btn button {
			margin-top: 25px;
			font-size: 13px;
			line-height: 20px;
			font-weight: 400;
			letter-spacing: normal;
		}

		#loginFormDiv .submit_btn .btn-primary:hover {
			border: none;
		}

		#loginFormDiv input {
			border-top-color: #d9d9d9;
			border-radius: .25rem;
		}

		#loginFormDiv input:focus {
			box-shadow: none;
			border-color: #d9d9d9;
		}

		#loginFormDiv .input-group input {
			border-radius: 0.25rem 0rem 0rem 0.25rem !important;
		}
		#loginFormDiv .login_form .input-group-btn .btn-eye svg {
			width: 15px;
			vertical-align: middle;
		}
		#loginFormDiv .input-group .input-group-btn .btn-eye {
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

		#loginFormDiv .form-group {
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
			<h1 class="text-center"></h1>
		</div>

		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login_beml">
			<div class="loginDiv widgetHeight ">
				<div class="bg-pattern">
					<div class="p-4 ">
					<img src="test/logo/logo_1(1).png" class="img-responsive user-logo" alt="{$COMPANY_LOGO->get('alt')}"/>
												<div>
							<span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
							<span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
						</div>

						<div id="loginFormDiv">
							<form method="POST" action="index.php" class="login_form">
								<input type="hidden" name="module" value="Users" />
								<input type="hidden" name="action" value="Login" />
								<div class="form-group">
									<label for="username">Username</label>
									<input class="form-control" id="usernameLogin" type="text" name="username" placeholder="UserName">
									<span class="failureMessage hide" id="validationMessageBadge"></span>
								</div>
								<div class="form-group">
									<label for="pwd">Password</label>
									<div id="forgotPasswordDiv1" class="input-group">
										<input type="password" class="form-control" name="password" id="password" placeholder="Password">
										<div class="input-group-btn">
											<div id="togglepassText" class="btn btn-soft-secondary btn-eye">
												<i data-feather="eye"></i>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group submit_btn">
									<button type="submit" class="button buttonBlue crm_btn btn btn-primary">Login</button><br>
								</div>
								<div class="text-center">
									<p><a id="linkforgotPasswordDiv">Forgot Password?</a></p>
								</div>
							</form>

							<!-- <form class="form-horizontal" method="POST" action="index.php">
    <input type="hidden" name="module" value="Users"/>
    <input type="hidden" name="action" value="Login"/>
    <div class="group">
    <input id="username" type="text" name="username" placeholder="Username">
    <span class="bar"></span>
    <label>Username</label>
    </div>
    <div class="group">
    <input id="password" type="password" name="password" placeholder="Password">
    <span class="bar"></span>
    <label>Password</label>
    </div>
    <div class="group">
    <button type="submit" class="button buttonBlue crm_btn">Sign in</button><br>
    <a class="forgotPasswordLink" style="color: #15c;display:none">forgot password?</a>
    </div>
    </form>
    -->
						</div>
						<div id="forgotPasswordDiv" class="hide">
							<form class="form-horizontal" id="forgotPasswordDivFormhol">
								<div class="group">
									<input  type="text"  id="fusername" name="username" placeholder="UserName">
									<span class="bar"></span>
									<label>UserName</label>
								</div>
								<div class="group">
									<input id="email" type="email" name="emailId" placeholder="Email">
									<span class="bar"></span>
									<label>Email</label>
								</div>
								<div class="group">
									<button type="submit" class="button buttonBlue forgot-submit-btn">Submit</button><br>
									<span>Please enter details and submit<a class="forgotPasswordLink pull-right" style="color: #15c;">Back</a></span>
								</div>
							</form>
						</div>
					</div>
				</div>

			</div>
			<div class="forgot_pwd">
				<a style="display:none" href="forgotPasswordDiv" class="forgotPasswordLink text-white-50" style="color: #15c;">Forgot password?</a>
			</div>
		</div>
		

		<div style="display:none" class="col-lg-1 hidden-xs hidden-sm hidden-md">
			<div class="separatorDiv"></div>
		</div>

		<div class="col-lg-5 hidden-xs hidden-sm hidden-md">
			<div style="display:none" class="marketingDiv widgetHeight">
				{if $JSON_DATA}
					<div class="scrollContainer">
						{assign var=ALL_BLOCKS_COUNT value=0}
						{foreach key=BLOCK_NAME item=BLOCKS_DATA from=$JSON_DATA}
							{if $BLOCKS_DATA}
								<div>
									<h4>{$BLOCKS_DATA[0].heading}</h4>
									<ul class="bxslider">
										{foreach item=BLOCK_DATA from=$BLOCKS_DATA}
											<li class="slide">
												{assign var=ALL_BLOCKS_COUNT value=$ALL_BLOCKS_COUNT+1}
												{if $BLOCK_DATA.image}
												<div class="col-lg-3" style="min-height: 100px;"><img src="{$BLOCK_DATA.image}" style="width: 100%;height: 100%;margin-top: 10px;" /></div>
												<div class="col-lg-9">
													{else}
													<div class="col-lg-12">
														{/if}
														<div title="{$BLOCK_DATA.summary}">
															<h3><b>{$BLOCK_DATA.displayTitle}</b></h3>
															{$BLOCK_DATA.displaySummary}<br><br>
															<a href="{$BLOCK_DATA.url}" target="_blank"><u>{$BLOCK_DATA.urlalt}</u></a>
														</div>
														{if $BLOCK_DATA.image}
													</div>
													{else}
												</div>
												{/if}
											</li>
										{/foreach}
									</ul>
								</div>
								{if $ALL_BLOCKS_COUNT neq $DATA_COUNT}
									<br>
									<hr>
								{/if}
							{/if}
						{/foreach}
					</div>
				{else}
					<div class="inActiveImgDiv">
						<div>
							<h4>Get more out of Vtiger with extensions from</h4>
							<h4>Vtiger Marketplace</h4>
						</div>
						<a href="https://marketplace.vtiger.com/app/listings" target="_blank" style="margin-right: 25px;"><img src="layouts/v7/resources/Images/extensionstore.png" style="width: 85%; height: 100%; margin-top: 25px;" /></a>
					</div>
				{/if}
			</div>
		</div>
	</div>
	<div class="modal fade" id="ShowSucess" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Email Sent</h4>
				</div>
				<div class="modal-body">
					<p> Password Reset Link Has Been Sent To Registered Email </p>
				</div>
				<div class="modal-footer">
					<button type="button" id="Redidirect" class="btn btn-default" data-dismiss="modal">Ok</button>
				</div>
			</div>
		</div>
  	</div>
	<div class="copy_right">
		<a href="#" class="text-white-50" style="color: #15c;">2021 © BizCRM theme by Biztechnosys</a>
	</div>
	<script>
		jQuery(document).ready(function() {

			var validationMessage = jQuery('#validationMessage');
			var forgotPasswordDiv = jQuery('#forgotPasswordDiv');

			var loginFormDiv = jQuery('#loginFormDiv');
			loginFormDiv.find('#password').focus();

			loginFormDiv.find('#linkforgotPasswordDiv').click(function() {
				loginFormDiv.toggleClass('hide');
				forgotPasswordDiv.toggleClass('hide');
				validationMessage.addClass('hide');
			});

			var showPasswordDiv = jQuery('#forgotPasswordDiv1');
			showPasswordDiv.find('#togglepassText').click(function() {
				let type = document.getElementById('password').type;
				if(type == 'password'){
					document.getElementById('password').type = 'text';
				} else {
					document.getElementById('password').type = 'password';
				}
			});

			forgotPasswordDiv.find('a').click(function() {
				loginFormDiv.toggleClass('hide');
				forgotPasswordDiv.toggleClass('hide');
				validationMessage.addClass('hide');
			});

			loginFormDiv.find('button').on('click', function() {
				var username = loginFormDiv.find('#usernameLogin').val();
				var password = jQuery('#password').val();
				var result = true;
				var errorMessage = '';
				if (username === '') {
					errorMessage = 'Please enter valid username';
					result = false;
				} else if (password === '') {
					errorMessage = 'Please enter valid password';
					result = false;
				}
				if (errorMessage) {
					validationMessage.removeClass('hide').text(errorMessage);
				}
				return result;
			});

			forgotPasswordDiv.find('button').on('click', function() {
				var username = jQuery('#forgotPasswordDiv #fusername').val();
				var email = jQuery('#email').val();

				var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
				var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

				var result = true;
				var errorMessage = '';
				if (username === '') {
					errorMessage = 'Please enter valid username';
					result = false;
				} else if (!emailFilter.test(email1) || email == '') {
					errorMessage = 'Please enter valid email address';
					result = false;
				} else if (email.match(illegalChars)) {
					errorMessage = 'The email address contains illegal characters.';
					result = false;
				}
				if (errorMessage) {
					validationMessage.removeClass('hide').text(errorMessage);
				}
				return result;
			});
			jQuery('input').blur(function(e) {
				var currentElement = jQuery(e.currentTarget);
				if (currentElement.val()) {
					currentElement.addClass('used');
				} else {
					currentElement.removeClass('used');
				}
			});

			var ripples = jQuery('.ripples');
			ripples.on('click.Ripples', function(e) {
				jQuery(e.currentTarget).addClass('is-active');
			});

			ripples.on('animationend webkitAnimationEnd mozAnimationEnd oanimationend MSAnimationEnd', function(e) {
				jQuery(e.currentTarget).removeClass('is-active');
			});
			loginFormDiv.find('#username').focus();

			var slider = jQuery('.bxslider').bxSlider({
				auto: true,
				pause: 4000,
				nextText: "",
				prevText: "",
				autoHover: true
			});
			jQuery('.bx-prev, .bx-next, .bx-pager-item').live('click', function() {
				slider.startAuto();
			});
			jQuery('.bx-wrapper .bx-viewport').css('background-color', 'transparent');
			jQuery('.bx-wrapper .bxslider li').css('text-align', 'left');
			jQuery('.bx-wrapper .bx-pager').css('bottom', '-15px');

			var params = {
				theme: 'dark-thick',
				setHeight: '100%',
				advanced: {
					autoExpandHorizontalScroll: true,
					setTop: 0
				}
			};
			jQuery('.scrollContainer').mCustomScrollbar(params);
		});
	</script>
	</div>
{/strip}