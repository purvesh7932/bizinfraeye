<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_WS_saveUser extends Mobile_WS_Controller
{

	function process(Mobile_API_Request $request)
	{
		$user = new Users();
		$values = json_decode($request->get('values'));

		$user->column_fields["first_name"] = $values->{'first_name'};
		$user->column_fields["last_name"] = $values->{'last_name'};
		$user->column_fields["user_name"] = $values->{'user_name'};
		$user->column_fields["status"] = $values->{'status'} ? $values->{'status'} : 'Active';
		$user->column_fields["is_admin"] = $values->{'is_admin'};
		$user->column_fields["user_password"] = $values->{'user_password'};
		$user->column_fields["confirm_password"] =$values->{'confirm_password'};
		$user->column_fields["tz"] = $values->{'tz'};
		// $user->column_fields["holidays"] = 'de,en_uk,fr,it,us,';
		// $user->column_fields["workdays"] = '0,1,2,3,4,5,6,';
		$user->column_fields["weekstart"] = $values->{'weekstart'};
		// $user->column_fields["namedays"] = '';
		$user->column_fields["currency_id"] = 1;
		$user->column_fields["reminder_interval"] = $values->{'reminder_interval'};
		$user->column_fields["reminder_next_time"] = $values->{'reminder_next_time'};
		$user->column_fields["date_format"] = $values->{'date_format'};
		$user->column_fields["hour_format"] = $values->{'hour_format'};
		$user->column_fields["start_hour"] = '08:00';
		$user->column_fields["end_hour"] = '23:00';
		$user->column_fields["imagename"] = '';
		$user->column_fields["internal_mailer"] = '0';
		$user->column_fields["activity_view"] = 'Today';
		$user->column_fields["lead_view"] = 'Today';
		$user->column_fields["email1"] = $values->{'email1'};
		$user->column_fields["roleid"] = 'H2';
		$user->column_fields["title"] = $values->{'title'};
		$user->column_fields["phone_fax"] = $values->{'phone_fax'};

		$user->save("Users");
		$result['result'] = 'true';
		$response = new Mobile_API_Response();
		$response->setResult($result);
		return $response;
	}
}
