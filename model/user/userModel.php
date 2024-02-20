<?php

function getUserInfo($fieldSearchArr=null, $fieldsStr="", $conn, $offset=null, $resultsPerPage=10)
{
	$res = array();
	$returnArr = array();
	$whereClause = "";
	
	//looping through array passed to create another array of where clauses
	foreach ($fieldSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} like '%{$searchVal}%'";
	}

	if(empty($fieldsStr))
		$fieldsStr = "*";

	$getUserInfoQuery = "SELECT {$fieldsStr} FROM crep_cms_user";
	if(!empty($whereClause))
		$getUserInfoQuery .= " WHERE {$whereClause}";
	if($offset!==null)
		$getUserInfoQuery .= " LIMIT {$offset}, {$resultsPerPage}";
//echo $getUserInfoQuery;exit;	
	$getUserInfoQueryResult = runQuery($getUserInfoQuery, $conn);
 
	if (!noError($getUserInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $getUserInfoQueryResult["errMsg"], null);
	}

	/* This function negotiates that an email must be fetched from the database. All user info is keyed by the user's email
	*  However, in case an email is not desired, like in the case of fetching counts, a default email of "anonymous" will be used
	*/
	while ($row = mysqli_fetch_assoc($getUserInfoQueryResult["dbResource"])) {
		if(!isset($row["email"]))
			$row["email"] = "anonymous";
			
		$res[$row["email"]] = $row;
	}
	
	return setErrorStack($returnArr, -1, $res, null);
}

function updateUserInfo($arrToUpdate=null, $fieldSearchArr=null, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($arrToUpdate)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to update cannot be empty", null);
	}
	if (empty($fieldSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	$arrToUpdate['updated_on'] = date("Y-m-d h:m:s");
	$setClause = "";
	$whereClause = "";
	
	//looping through array passed to create another array of where clauses
	foreach ($fieldSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = '{$searchVal}'";
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($arrToUpdate as $colName=>$setVal) {
		if(!empty($setClause))
			$setClause .= ", ";
		$setClause .= "{$colName} = '{$setVal}'";
	}

	$updateUserQuery = "UPDATE crep_cms_user SET {$setClause} WHERE {$whereClause}";
	$updateUserQueryResult = runQuery($updateUserQuery, $conn);
	if (noError($updateUserQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $updateUserQueryResult["errMsg"], null);
	}
}

function createUser($userArr, $fieldsStr, $conn)
{
	$res = array();
	$returnArr = array();

	if (empty($userArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to insert cannot be empty", null);
	}
	if (empty($fieldsStr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Fields String cannot be empty", null);
	}

	$allValuesStr = "";	
	$insertUserQuery = "INSERT INTO crep_cms_user ({$fieldsStr}) VALUES ";
	foreach ($userArr as $email=>$userDetails) {
		if (!empty($valuesStr)) {
			$allValuesStr .= ",";
		}
		$valuesStr = "";
		foreach ($userDetails as $colName=>$value) {
			$valuesStr .= $value.",";
		}
		$valuesStr = rtrim($valuesStr, ",");
		$allValuesStr .= "({$valuesStr})";
	}
	$insertUserQuery .= $allValuesStr;

	@unlink("polo_create_user.txt");
    file_put_contents("polo_create_user.txt", $insertUserQuery);
    @chmod("polo_create_user.txt", 0777);


	$insertUserQueryResult = runQuery($insertUserQuery, $conn);

	if (noError($insertUserQueryResult)) {
		return setErrorStack($returnArr, -1, $res, null);
	} else {
		return setErrorStack($returnArr, 3, $insertUserQueryResult["errMsg"], null);
	}
}

function deleteUserInfo($userSearchArr, $conn)
{
	$returnArr = array();
	$whereClause = "";

	if (empty($userSearchArr)) {
		return setErrorStack($returnArr, 4, getErrMsg(4)." Array to search cannot be empty", null);
	}
	
	//looping through array passed to create another array of where clauses
	foreach ($userSearchArr as $colName=>$searchVal) {
		if(!empty($whereClause))
			$whereClause .= " AND ";
		$whereClause .= "{$colName} = {$searchVal}";
	}

	$deleteUserInfoQuery = "DELETE FROM crep_cms_user";
	if(!empty($whereClause))
		$deleteUserInfoQuery .= " WHERE {$whereClause}";
	
	$deleteUserInfoQueryResult = runQuery($deleteUserInfoQuery, $conn);
	if (!noError($deleteUserInfoQueryResult)) {
		return setErrorStack($returnArr, 3, $deleteUserInfoQueryResult["errMsg"], null);
	}

	return setErrorStack($returnArr, -1, getErrMsg(-1), null);

}

function createPasswordChangedEmail($userName, $ipAddress, $rootUrl)
{
	return 
	'<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
		<tbody>
			<tr>
				<td align="left">
					<table border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td style="font-family: sans-serif;font-size: 14px;vertical-align: top;box-sizing: border-box">
										<p>Dear '.$userName.',</p>
										<p>Your account password has been recently changed on the Nirvana Digital</p>
										<p>If it wasn&#39;t you, we suggest you change your password immediately.</p>
										<p>Here are some more details about the operation:</p>
										<ul>
											<li>IP Address: '.$ipAddress.'</li>
											<li>Time of event: '.date("D jS M, Y h:m:s A").'</li>
										</ul>
										<p>For any questions, please feel free to reach out to us at the contact details mentioned below.</p>
										<p>&nbsp;</p>
										<p>Regards,</p>
										<p>&nbsp;</p>
										<p>Nirvana Digital Team</p>
										<p><img src="'.$rootUrl.'assets/img/nirvana_logo.jpg" style="width: 1.3in, height: 1.2in"></p>
										<p>Nirvana Digital</p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';
}

function createUserActivationEmail($url)
{
	return '<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary"><tbody>
				<tr>
					<td align="left">
						<table border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td style="font-family: sans-serif;font-size: 14px;vertical-align: top;box-sizing: border-box">
											<p>Dear Sir/Madam,</p>
											<p>Welcome to the online portal of Nirvana Digital</p>
		
											<p>Please click the link below to authenticate your account</p>
											<p>
												<a href="'.$url.'" target="_blank" style="background-color:#3498db;border-color:#3498db;color:#fff;border:1px solid #3498db;border-radius:5px;cursor:pointer;display:inline-block;font-weight:700;margin:0;padding:12px 25px;text-decoration:none">Click here to activate your user account.</a>
											</p>
											<p>Once the account is authenticated, you will get another email to set up the password. If you do not receive the email, please click the forgot password link and finish the set-up of the account.</p>
											<p>In the first week of every month, you will get an email stating that the portfolio snapshot statement is now online.</p>
											<p>For any questions, please feel free to reach out to us at the below-the portfolio snapshot statement is now online.</p>
											<p>&nbsp;</p>
											<p>Regards,</p>
											<p>&nbsp;</p>
											
											<p><img src="http://investor.buoyantcap.com/assets/img/buoyant-capital%20final%20logo.png" style="width: 1.3in,
											<p><span>Nirvana</span><span>Digital</span>
											
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>';
}

function createResetPasswordEmail($userName, $resetPasswordUrl, $rootUrl, $resetPasswordToken)
{
	return 
	'<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
		<tbody>
			<tr>
				<td align="left">
					<table border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td style="font-family: sans-serif;font-size: 14px;vertical-align: top;box-sizing: border-box">
										<p>Dear '.$userName.',</p>
										<p>A request to reset password was placed on the Nirvana Digital</p>
										<p>If it wasn&#39;t you, we suggest you change your password immediately.</p>
										<p>If it was you, to reset password, please click the following button:</p>
										<p>
											<a href="'.$resetPasswordUrl.'" target="_blank" style="background-color:#3498db;border-color:#3498db;color:#fff;border:1px solid #3498db;border-radius:5px;cursor:pointer;display:inline-block;font-weight:700;margin:0;padding:12px 25px;text-decoration:none">Click here to reset your password.</a>
										</p>
										<p>For any questions, please feel free to reach out to us at the contact details mentioned below.</p>
										<p>&nbsp;</p>
										<p>Regards,</p>
										<p>&nbsp;</p>
										<p>Nirvana Digital Team</p>
										<p><img src="'.$rootUrl.'assets/img/nirvana_logo.jpg" style="width: 1.3in, height: 1.2in"></p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';
}

function createAccountActivationEmail($userName, $loginUrl, $password)
{
	return '<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary"><tbody>
				<tr>
					<td align="left">
						<table border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td style="font-family: sans-serif;font-size: 14px;vertical-align: top;box-sizing: border-box">
											<p>Dear '.$userName.',</p>
											<p>Congratulations! Your account has been activated on the online portal of Nirvana Digital</p>
											<p>Your temporary password is: <span style="font-weight: bold">'.$password.'</span></p>
											<p>Please login to the portal</p>
											<p>
												<a href="'.$loginUrl.'" target="_blank" style="background-color:#3498db;border-color:#3498db;color:#fff;border:1px solid #3498db;border-radius:5px;cursor:pointer;display:inline-block;font-weight:700;margin:0;padding:12px 25px;text-decoration:none">Login.</a>
											</p>
											<p>For any questions, please feel free to reach out to us at the information mentioned below.</p>
											<p>&nbsp;</p>
											<p>Regards,</p>
											<p>&nbsp;</p>
										
											<p><img src="http://investor.buoyantcap.com/assets/img/nirvana_logo.jpg" style="width: 1.3in, height: 1.2in"></p>
											<p><span>Nirvana</span><span>Digital</span>
											
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>';
}

?>
