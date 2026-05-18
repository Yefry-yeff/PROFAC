$p = "C:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\storage\framework\views\14e425a1055fd31904417969c2bee2887ad5fc32.php"
$c = [System.IO.File]::ReadAllText($p)
Write-Host "Len: $($c.Length)"
$i1 = $c.IndexOf("startPush(''scripts'')")
Write-Host "startPush: $i1"
if($i1 -ge 0){
  $s = [Math]::Max(0,$i1-200); $l = [Math]::Min($c.Length-$s, 700)
  Write-Host "--- STARTPUSH ---"
  Write-Host $c.Substring($s,$l)
}
$i2 = $c.IndexOf("document).ready")
Write-Host "ready: $i2"
$i3 = $c.IndexOf("fpEmpleado")
Write-Host "fpEmpleado: $i3"
if($i3 -ge 0){
  $s3 = [Math]::Max(0,$i3-100); $l3 = [Math]::Min($c.Length-$s3, 500)
  Write-Host "--- FPEMPLEADO ---"
  Write-Host $c.Substring($s3,$l3)
}
