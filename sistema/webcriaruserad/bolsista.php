<?php

session_start();
if(!$_SESSION['userdn']) {
  header('Location: http://createuserad.contoso.com');
  exit();
}

$local = $_POST['local'];
$userad1 = $_POST['1userad'];
$userad2 = $_POST['2userad'];
$userad3 = $_POST['3userad'];
$cpf = $_POST['cpf'];
$telefone = $_POST['telefone'];
$sala = $_POST['sala'];
$date = $_POST['date'];

//echo $local;

$psScriptPath1 = "/var/www/html/createuserad/teste.ps1";
$query1 = shell_exec("pwsh -ExecutionPolicy Bypass -NoProfile -InputFormat none -command $psScriptPath1 -date '$date' ");

$query2 = intval($query1); 
echo gettype($query2);

$AD_server = "contoso.com.br";

$ds = ldap_connect($AD_server);
if ($ds) {
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3); // IMPORTANT
    $result = ldap_bind($ds, "CN=createuserad,OU=Sistemas,OU=Contas de Servico,OU=contoso,DC=contoso,DC=com,DC=br","PassWd01"); //BIND
if (!$result)
{
echo 'Not Binded';
}

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
$user = $_SESSION['userdn'];
#echo $user;
$t45n = (explode("=",$user));
$t46n = (explode(",",$t45n[1]));
#echo $t46n[0];
#echo $ip_address;
  //echo $user;
  //echo $password;
  //echo $domain;
  $data = date("d/m/Y-H:i:s");
  $usuario = $t46n[0];
  $InfoLog = $usuario." ".$data;
  
  
  $array = explode(' ', $InfoLog);
  $logsi = "[ ".$array[0]." ".$array[1]. " | Ação: Criou o usuario Bolsista, nome:".$userad3.", IP: ". $ip_address ." ]\r\n";
  #echo $logsi;
 
        $Logs = $logsi;
        $dat = date("dmY");
        //Variável arquivo armazena o nome e extensão do arquivo.
        $arquivo = "/var/www/html/createuserad/log/".$dat."_".$t46n[0].".txt";
         
        //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
        $fp = fopen($arquivo, "a+",0);
       
        //Escreve no arquivo aberto.
        fwrite($fp, $Logs, strlen($Logs));
         
        //Fecha o arquivo.
        fclose($fp);
  
  


$ldaprecord['objectclass'][0] = "user";
$ldaprecord['objectclass'][1] = "posixaccount";
$ldaprecord['objectclass'][2] = "top";
$ldaprecord['cn'] = $userad3;
$ldaprecord['givenname'] = $userad1;
$ldaprecord['sn'] = $userad2;
		$ldaprecord['description'] = 'Bolsista';
		$ldaprecord['sAMAccountName'] = 'B'.$cpf;
		$ldaprecord['userPrincipalName'] = 'B'.$cpf.'@contoso.com.br';
		
$ldaprecord['displayname'] = $userad3;
     $ldaprecord['uidnumber'] = '1005';
     $ldaprecord['gidnumber'] = '501';
     $ldaprecord['userpassword'] = "PassWD01";
//$ldaprecord['userpassword'] = `{MD5}` . base64_encode(pack(`H*`,`pssWd01`))
	 $ldaprecord['userAccountControl'] = "544";
$ldaprecord['loginshell'] = '/bin/sh';
     $ldaprecord['homedirectory'] = '/home/users/nb';
	 $ldaprecord['physicalDeliveryOfficeName'] = $sala;
     $ldaprecord['shadowexpire'] = '-1';
     $ldaprecord['shadowflag'] = '0';
     $ldaprecord['shadowwarning'] = '7';
     $ldaprecord['shadowmin'] = '8';
     $ldaprecord['shadowmax'] = '999999';
     $ldaprecord['shadowlastchange'] = '10877';
	 $ldaprecord['accountExpires'] = $query2;
	 
if($local == 'Brasilia'){
	$ldaprecord['telephoneNumber'] = '(61) 0000-'.$telefone;
     $ldaprecord['postalcode'] = '70390';
     $ldaprecord['l'] = 'Brasília';
     $ldaprecord['o'] = 'example';
	 $ldaprecord['streetAddress'] = "endereço df";
	 $ldaprecord['st'] = "DF";
	 $ldaprecord['co'] = "Brazil";
	 
}else{
	$ldaprecord['telephoneNumber'] = '(21) 0000-'.$telefone;
	 $ldaprecord['postalcode'] = '20071';
     $ldaprecord['l'] = 'Rio de Janeiro';
     $ldaprecord['o'] = 'example';
	 $ldaprecord['streetAddress'] = "endereco-rj";
	 $ldaprecord['st'] = "RJ";
	 $ldaprecord['co'] = "Brazil";
	
}
	 
	 

if($local == 'Brasilia'){
			#$base_dn = "cn=".$userad3.",OU=b,OU=df,OU=Homologacao,OU=Desktops,OU=Computadores,OU=IPEA_DF,OU=contoso,DC=ipea,DC=gov,DC=br";
      $base_dn = "cn=".$userad3.",OU=Bolsistas,OU=Usuarios,OU=contoso_DF,OU=IPEAcontoso,DC=contoso,DC=gov,DC=br";

}else{
			#$base_dn = "cn=".$userad3.",OU=b,OU=rj,OU=Homologacao,OU=Desktops,OU=Computadores,OU=IPEA_DF,OU=contoso,DC=ipea,DC=gov,DC=br";
      $base_dn = "cn=".$userad3.",OU=Bolsistas,OU=Usuarios,OU=contoso_RJ,OU=contoso,DC=contoso,DC=gov,DC=br";

}
				
$r = ldap_add($ds, $base_dn, $ldaprecord);
			   if ($r)
			   {
			   echo "<SCRIPT LANGUAGE='JavaScript'>
        window.alert('Usuario foi criado conforme Solicitado. ');
		window.location.href = 'http://createuserad.contoso.gov.br/entrada.php';
        </SCRIPT>";
			   }
			   else
			   {
			   echo ldap_errno($ds) ;
			   }
			} else {
				echo "cannot connect to LDAP server at $AD_server.";
			}

?>
