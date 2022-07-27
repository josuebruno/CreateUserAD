param(
[string]$date
)

#$date = "2040-05-25"

$arraydate = $date -split "-"

$ano = $arraydate[0]
$mes = $arraydate[1]
$dia = $arraydate[2]

$date = $ano+$mes+$dia

$dateparsed = [datetime]::ParseExact($date,'yyyyMMdd',$null)
$dateparsed = $dateparsed.ToFileTime()

$dateparsed