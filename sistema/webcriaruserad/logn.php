<?php


$user = $_POST['usuario'];
$password = $_POST['senha'];
$host = 'ipservidorDC';
$domain = 'contoso.com.BR';
$basedn = 'DC=contoso,DC=com,DC=br';
$group = 'grupoAD';


if (!empty($_SERVER['HTTP_CLIENT_IP']))   
    {
      $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
    {
      $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
  else
    {
      $ip_address = $_SERVER['REMOTE_ADDR'];
    }
  #echo $ip_address;
  //echo $user;
  //echo $password;
  //echo $domain;
    
  $data = date("d/m/Y-H:i:s");
  $usuario = $user;
  $InfoLog = $usuario." ".$data;
  
  #CN=Josue Soares Bruno - CONTA DE SERVIÇO,OU=TI,OU=DF - SIA,OU=Contas Administrativas,OU=Departamento de Tecnologia,DC=call,DC=br 04/12/2019
  $array = explode(' ', $InfoLog);
  $logsi = "[ ".$array[0]." ".$array[1]. " | Ação: Logou ou realizou uma tentativa de logon, IP: ". $ip_address ." ]\r\n";
  #echo $logsi;
 
				$Logs = $logsi;
				$dat = date("dmY");
				//Variável arquivo armazena o nome e extensão do arquivo.
				$arquivo = "/var/www/html/createuserad/log/".$dat."_".$user.".txt";
				 
				//Variável $fp armazena a conexão com o arquivo e o tipo de ação.
				$fp = fopen($arquivo, "a+",0);
			 
				//Escreve no arquivo aberto.
				fwrite($fp, $Logs, strlen($Logs));
				 
				//Fecha o arquivo.
				fclose($fp);
  
  
  
  
  
  
  


$ad = ldap_connect($domain) or die('Could not connect to LDAP server.');
ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
@ldap_bind($ad, "{$user}@{$domain}", $password) or die(
		
		"<SCRIPT LANGUAGE='JavaScript'>
        window.alert('Usuario ou senha incorreta!');
		window.location.href = 'http://createuserad.contoso.com.br';
        </SCRIPT>");
$userdn = getDN($ad, $user, $basedn);
if (checkGroupEx($ad, $userdn, getDN($ad, $group, $basedn))) {
    session_start();
        $_SESSION['userdn'] = $userdn;   
        header("Location:entrada.php");
} else {
    echo "<SCRIPT LANGUAGE='JavaScript'>
        window.alert('Usuario não autorizado entre em contato com o administrador do Sistema! ');
		window.location.href = 'http://createuserad.contoso.com.br';
        </SCRIPT>";
		
}
ldap_unbind($ad);

/*
* This function searchs in LDAP tree ($ad -LDAP link identifier)
* entry specified by samaccountname and returns its DN or epmty
* string on failure.
*/
function getDN($ad, $samaccountname, $basedn) {
    $attributes = array('dn');
    $result = ldap_search($ad, $basedn,
        "(samaccountname={$samaccountname})", $attributes);
    if ($result === FALSE) { return ''; }
    $entries = ldap_get_entries($ad, $result);
    if ($entries['count']>0) { return $entries[0]['dn']; }
    else { return ''; };
}

/*
* This function retrieves and returns CN from given DN
*/
function getCN($dn) {
    preg_match('/[^,]*/', $dn, $matchs, PREG_OFFSET_CAPTURE, 3);
    return $matchs[0][0];
}

/*
* This function checks group membership of the user, searching only
* in specified group (not recursively).
*/
function checkGroup($ad, $userdn, $groupdn) {
    $attributes = array('members');
    $result = ldap_read($ad, $userdn, "(memberof={$groupdn})", $attributes);
    if ($result === FALSE) { return FALSE; };
    $entries = ldap_get_entries($ad, $result);
    return ($entries['count'] > 0);
}

/*
* This function checks group membership of the user, searching
* in specified group and groups which is its members (recursively).
*/
function checkGroupEx($ad, $userdn, $groupdn) {
    $attributes = array('memberof');
    $result = ldap_read($ad, $userdn, '(objectclass=*)', $attributes);
    if ($result === FALSE) { return FALSE; };
    $entries = ldap_get_entries($ad, $result);
    if ($entries['count'] <= 0) { return FALSE; };
    if (empty($entries[0]['memberof'])) { return FALSE; } else {
        for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
            if ($entries[0]['memberof'][$i] == $groupdn) { return TRUE; }
            elseif (checkGroupEx($ad, $entries[0]['memberof'][$i], $groupdn)) { return TRUE; };
        };
    };
    return FALSE;
}

?>