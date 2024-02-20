<?php
	
	function generateSalt(){
	    $salt_length = 12;
	    $salt = substr(md5(uniqid()), 0, $salt_length);
	    return $salt;
	}

	function encryptPassword($pwd, $salt){
	    $hashed_password = md5($salt . $pwd);
	    return $hashed_password;
	}

function generateToken($length)
{   
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;	
}
    
	 function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds'){
        
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
        $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
        $sets[] = '!#@$%&';

        $all = '';
        $password = '';
        foreach($sets as $set)
        {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
        $password .= $all[array_rand($all)];

        $password = str_shuffle($password);

        if(!$add_dashes)
        return $password;

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
    

?>