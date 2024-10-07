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
								{foreach from=$USERBLOCKS item=blockitem key=blockkey}
									<h4 style="text-align: center;">{{$blockitem['label']}}</h4>
									{foreach from=$blockitem['fields'] item=item key=key}
									<div class="">
										{if  $item['name'] eq 'assigned_user_id'}
										{elseif $item['name'] eq 'cust_role' || $item['name'] eq 'designaion' || $item['name'] eq 'sub_service_manager_role' }
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'} style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<select {if $item['mandatory'] eq true} data-rule-required="true"{/if} id="{{$item['name']}}1" onchange="myFunction(event)" id="{{$item['name']}}" class="form-control select2 select2-container" data-fieldtype="picklist"  name="{{$item['name']}}"  portal-select>
														<option value="">
														</option>
													</select>
												</div>
											</span>
										{elseif $item['type']['name'] eq 'picklist' }
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'} style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<select {if $item['mandatory'] eq true} data-rule-required="true"{/if} onchange="myFunction(event)" id="{{$item['name']}}" class="select2 select2-container form-control pick{{$item['name']}}" data-fieldtype="picklist"  name="{{$item['name']}}"  portal-select>
														<option value="">
														</option>
														{foreach from=$item.type.picklistValues item=itemopt}
															<option value="{{$itemopt.value}}">
																{{$itemopt.label}}
															</option>
														{/foreach}
													</select>
												</div>
											</span>
										{elseif $item['type']['name'] eq 'phone'}
											<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
											<div class="col-sm-8 form-group">
												<input name="{{$item['name']}}1" {if $item['mandatory'] eq true} data-rule-required="true"{/if} {literal}data-specific-rules='[{"name":"itivalidate"}]'{/literal} onkeypress='return event.charCode >= 48 && event.charCode <= 57'  id="phone" type="tel" onkeypress='return event.charCode >= 48 && event.charCode <= 57' placeholder="" class="form-control"   autofill="autofill">
											    <input type="hidden" name="{{$item['name']}}" id='phone2'/>
												<div>
													<span id="phone_valid-msg" class="hide text-success">Phone Number is valid</span>
													<span id="phone_error-msg" class="hide text-danger">Invalid number</span>
												</div>
											</div>
										{elseif $item['name'] eq 'date_of_birth'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] == 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input  {literal} data-specific-rules='[{"name":"date_of_birthValidate"}]' {/literal} {if $item['mandatory'] eq true} data-rule-required="true"{/if} class="dateselectorDOB form-control" type="text"  class="form-control"  name="{{$item['name']}}"  autofill="autofill">
												</div>
											</span>
										{elseif $item['name'] eq 'date_of_joining'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] == 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input  {literal} data-specific-rules='[{"name":"date_of_joiningValidate"}]' {/literal} {if $item['mandatory'] eq true} data-rule-required="true"{/if} class="dateselectoDOJ form-control" type="text"  class="form-control"  name="{{$item['name']}}"  autofill="autofill">
												</div>
											</span>
										{elseif $item['type']['name'] eq 'date'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] == 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}} {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input {if $item['mandatory'] eq true} data-rule-required="true"{/if} class="dateselector form-control" type="text"  class="form-control"  name="{{$item['name']}}"  autofill="autofill">
												</div>
											</span>
										{elseif $item['type']['name'] eq 'email'}
											<h5 style="text-align: center;margin-top: 1px;margin-bottom: 5px;">(OTP Will Be Sent To Verify your Email)</h5>
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input data-rule-email="true" {if $item['mandatory'] eq true} data-rule-required="true"{/if} type="text" class="form-control"  name="{{$item['name']}}" autofill="autofill">
												</div>
											</span>
										{elseif $item['name'] eq 'badge_no'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input maxlength="5" type="number" {literal}data-specific-rules='[{"name":"onlyinteger"},{"name" : "maxsizeInSignUP", "params":"5"},{"name" : "minsize", "params":"5"}]'{/literal} {if $item['mandatory'] eq true} data-rule-required="true"{/if} type="text" class="form-control"  name="{{$item['name']}}" autofill="autofill">
												</div>
											</span>
										{elseif $item['type']['name'] eq 'integer'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input {literal}data-specific-rules='[{"name":"onlyinteger"},{"name" : "maxsizeInSignUP", "params":"5"},{"name" : "minsize", "params":"5"}]'{/literal} {if $item['mandatory'] eq true} data-rule-required="true"{/if} type="text" class="form-control"  name="{{$item['name']}}" autofill="autofill">
												</div>
											</span>
										{elseif $item['name'] eq 'user_password'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input id="password_1" {if $item['mandatory'] eq true} data-rule-required="true"{/if} {literal}data-specific-rules='[{"name":"passwordmix"},{"name" : "maxsizeInSignUP", "params":"18"},{"name" : "allowOnlyCertainSpecialCharcters"}]'{/literal} type="password" class="form-control"  name="{{$item['name']}}" autofill="autofill">
													<span id="togglePassword1" style="margin-top: -25px ;margin-right:10px;float: right;cursor: pointer;" class="glyphicon glyphicon-eye-open"></span> 
												</div>
											</span>
										{elseif $item['name'] eq 'confirm_password'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input id="password_2" {if $item['mandatory'] eq true} data-rule-required="true"{/if} {literal}data-specific-rules='[{"name":"passwordmix"},{"name" : "maxsizeInSignUP", "params":"18"},{"name" : "checkPasswordMatching"},{"name" : "allowOnlyCertainSpecialCharcters"}]'{/literal} type="password" class="form-control"  name="{{$item['name']}}" autofill="autofill">
													<span id="togglePassword2" style="margin-top: -25px ;margin-right:10px;float: right;cursor: pointer;" class="glyphicon glyphicon-eye-open"></span> 
												</div>
											</span>
											<h6 style="text-align: center;margin-top: 10px;margin-bottom: 10px;">
												<b> Password Should Be Minimum 8 Charcters With Atleast <br>
													1 Upper Case , 1 Lower Case , 1 Number And  <br>
													1 Special Charcter(@ , #, $, &, *)
												</b>
											</h6>
										{elseif $item['type']['name'] eq 'stringonlychars'}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input {if $item['mandatory'] eq true} data-rule-required="true"{/if} {literal}data-specific-rules='[{"name":"stringonlychars"}]'{/literal} type="text" class="form-control"  name="{{$item['name']}}" autofill="autofill">
												</div>
											</span>
										{else}
											<span id="{{$item['name']}}" {if $item['initialDisplay'] eq 'no'}style="display: none" {/if}>
												<label for="{{$item['name']}}" class="col-sm-4 control-label">{{$item['label']}}  {if $item['mandatory'] eq true} <span class="text-danger">*</span>{/if}</label>
												<div class="col-sm-8 form-group">
													<input {if $item['mandatory'] eq true} data-rule-required="true"{/if} type="text" class="form-control"  name="{{$item['name']}}" autofill="autofill">
												</div>
											</span>
										{/if}
									</div>
									{/foreach}
								{/foreach}
						</div>
					</div>
					<div class="submit_btn form-group" style="padding : 20px;padding-top : 0px">
						<div class="col-sm-12">
							<button id="SignUPSubmitButton" class="button buttonBlue crm_btn btn btn-primary">Sign Up</button><br>
							<br>
							<a href = "index.php?module=Users&parent=Settings&view=Login" class="btn  btn-primary" ><span class="glyphicon glyphicon-chevron-left"></span>  Back </a>
						</div>
					</div>
				</div>
			</form>
			<div class="forgot_pwd">
				<a style="display:none" href="forgotPasswordDiv" class="forgotPasswordLink text-white-50" style="color: #15c;">Forgot password?</a>
			</div>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<div id="bemlCarousel" class="carousel slide" data-ride="carousel">
				<ol class="carousel-indicators">
					<li data-target="#bemlCarousel" data-slide-to="0" class="active"></li>
					<li data-target="#bemlCarousel" data-slide-to="1"></li>
				</ol>
				<div class="carousel-inner">
					<div class="item active">
						<img src="test/logo/slider/BEML_CRM_SLIDES_SR.jpg" alt="Los Angeles">
					</div>
					<div class="item">
						<img src="test/logo/slider/BEML_CRM_CCH.jpg" alt="Los Angeles">
					</div>
				</div>
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
				{/if}
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
				<h4 class="modal-title">Registration Is Successful</h4>
				</div>
				<div class="modal-body">
					<p> Thank you for your valuable registration</p>
					<p> Verification pending from BEML</p>
					<p> After succesful verification, you will be communicated through SMS/Email</p>
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
	<div class="copy_right">
		<a href="#" class="text-white-50" style="color: #15c;">2021 © BizCRM theme by Biztechnosys</a>
	</div>
	<script type="text/javascript" src="{vresource_url('layouts/v7/resources/v7_client_compat.js')}"></script>
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Users/resources/intlTelInput.js')}"></script>
	<script src="{vresource_url('layouts/v7/modules/Users/resources/ig-jquery-ui.js')}"></script>
	<script>
		function myFunction(event) {
			let value = event.target.value;
			let name = event.target.name;

			if (name == 'office') {
				$('#cust_role1').children().remove().end();
				$("#sub_service_manager_role").css({ "display": "none" });
				$('#cust_role1')
					.append($('<option>', { 'value': '' })
						.text(''));
				if (value == 'Corporate Office-BEML Soudha') {
					$('#cust_role1')
						.append($('<option>', { 'value': 'BEML Management' })
							.text('BEML Management'));
				} else if (value == 'Marketing Headquarter-Unity Buildings') {
					$('#cust_role1')
						.append($('<option>', { 'value': 'BEML Management' })
							.text('BEML Management'));
					$('#cust_role1')
						.append($('<option>', { 'value': 'BEML Marketing HQ' })
							.text('BEML Marketing HQ'));
				} else if (value == 'Regional Office' || value == 'District Office' || value == 'Activity Centre' || value == 'Service Centre') {
					$('#cust_role1')
						.append($('<option>', { 'value': 'Service Manager' })
							.text('Service Manager'));
					$('#cust_role1')
						.append($('<option>', { 'value': 'Service Engineer' })
							.text('Service Engineer'));
				} else if (value == 'Production Division') {
					$('#cust_role1')
						.append($('<option>', { 'value': 'BEML Management' })
							.text('BEML Management'));
					$('#cust_role1')
						.append($('<option>', { 'value': 'Divisonal Service Support' })
							.text('Divisional Service Support'));
				} else if (value == 'International Business Division-New Delhi') {
					$('#cust_role1')
						.append($('<option>', { 'value': 'BEML Management' })
							.text('BEML Management'));
					$('#cust_role1')
						.append($('<option>', { 'value': 'Service Manager' })
							.text('Service Manager'));
					$('#cust_role1')
						.append($('<option>', { 'value': 'Service Engineer' })
							.text('Service Engineer'));
				}

			}

			if (name == 'cust_role') {
				$('#designaion1').children().remove().end();
				$('#designaion1')
					.append($('<option>', { 'value': '' })
						.text(''));
				if (value == 'Service Manager' || value == 'Divisonal Service Support' || value == 'BEML Marketing HQ') {
					$('#designaion1')
						.append($('<option>', { 'value': 'Chief General Manager' })
							.text('Chief General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'General Manager' })
							.text('General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Deputy General Manager' })
							.text('Deputy General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Assistant General Manager' })
							.text('Assistant General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Manager' })
							.text('Senior Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Manager' })
							.text('Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Assistant Manager' })
							.text('Assistant Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Engineer' })
							.text('Engineer'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Assistant Engineer' })
							.text('Assistant Engineer'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-6' })
							.text('Senior Supervisor-S-6'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-55' })
							.text('Senior Supervisor-S-5'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-4' })
							.text('Senior Supervisor-S-4'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Supervisor- S-3' })
							.text('Supervisor- S-3'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Joint Supervisior-S-2' })
							.text('Joint Supervisior-S-2'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Deputy Supervisor-S-1' })
							.text('Deputy Supervisor-S-1'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Master Skilled Technician-Gr.-E' })
							.text('Master Skilled Technician-Gr.-E'));
					$('#designaion1')
						.append($('<option>', { 'value': 'High Skilled Technician-Gr.-D' })
							.text('High Skilled Technician-Gr.-D'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Technician-Gr.-C' })
							.text('Senior Technician-Gr.-C'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Technician-Gr.-B' })
							.text('Technician-Gr.-B'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Helper- Gr-A' })
							.text('Helper- Gr-A'));
				}

				if (value == 'Service Engineer') {
					$('#designaion1')
						.append($('<option>', { 'value': 'Manager' })
							.text('Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Assistant Manager' })
							.text('Assistant Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Engineer' })
							.text('Engineer'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Assistant Engineer' })
							.text('Assistant Engineer'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-6' })
							.text('Senior Supervisor-S-6'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-55' })
							.text('Senior Supervisor-S-5'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Supervisor-S-4' })
							.text('Senior Supervisor-S-4'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Supervisor- S-3' })
							.text('Supervisor- S-3'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Joint Supervisor-S-2' })
							.text('Joint Supervisor-S-2'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Deputy Supervisor-S-1' })
							.text('Deputy Supervisor-S-1'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Master Skilled Technician-Gr.-E' })
							.text('Master Skilled Technician-Gr.-E'));
					$('#designaion1')
						.append($('<option>', { 'value': 'High Skilled Technician-Gr.-D' })
							.text('High Skilled Technician-Gr.-D'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Senior Technician-Gr.-C' })
							.text('Senior Technician-Gr.-C'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Technician-Gr.-B' })
							.text('Technician-Gr.-B'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Helper- Gr-A' })
							.text('Helper- Gr-A'));
				}

				if (value == 'BEML Management') {
					$('#designaion1')
						.append($('<option>', { 'value': 'Chairman & Managing Director' })
							.text('Chairman & Managing Director'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Director(Mining & Construction Business)' })
							.text('Director(Mining & Construction Business)'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Director(Defence Business)' })
							.text('Director(Defence Business)'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Director' })
							.text('Director'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Executive Director' })
							.text('Executive Director'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Chief General Manager' })
							.text('Chief General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'General Manager' })
							.text('General Manager'));
					$('#designaion1')
						.append($('<option>', { 'value': 'Deputy General Manager' })
							.text('Deputy General Manager'));
				}
			}

			if (name == 'cust_role') {
				if (value == 'Service Manager') {
					$("#sub_service_manager_role").css({ "display": "inline" });
				} else {
					$("#sub_service_manager_role").css({ "display": "none" });
				}
			}
			if (name == 'office') {
				let fieldName = value.toLowerCase();
				fieldName = fieldName.replace(/ /g, "_");
				$("#" + fieldName).css({ "display": "inline" });
				let fieldsArray = Array('district_office', 'service_centre', 'activity_centre', 'production_division', 'regional_office');
				let fieldsArrayLength = fieldsArray.length;
				for (let i = 0; i < fieldsArrayLength; i++) {
					if (fieldsArray[i] != fieldName) {
						$("#" + fieldsArray[i]).css({ "display": "none" });
					}
				}
			}
		}
	</script>
	</div>
{/strip}