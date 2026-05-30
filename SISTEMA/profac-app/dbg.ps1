$content = Get-Content 'C:\laragon\www\Valencia\PROFAC\SISTEMA\profac-app\storage\framework\views\14e425a1055fd31904417969c2bee2887ad5fc32.php' -Raw
Write-Host "File Length: $($content.Length)"

$idx = $content.IndexOf("startPush('scripts')")
Write-Host "startPush index: $idx"
if ($idx -ge 0) {
    $start = [Math]::Max(0, $idx - 200)
    $len = [Math]::Min($content.Length - $start, 700)
    Write-Host "=== CONTEXT AROUND startPush ==="
    Write-Host $content.Substring($start, $len)
}

$idx2 = $content.IndexOf('document).ready')
Write-Host "document).ready index: $idx2"

$idx3 = $content.IndexOf('fpEmpleado')
Write-Host "fpEmpleado index: $idx3"
if ($idx3 -ge 0) {
    $start3 = [Math]::Max(0, $idx3 - 100)
    $len3 = [Math]::Min($content.Length - $start3, 500)
    Write-Host "=== CONTEXT AROUND fpEmpleado ==="
    Write-Host $content.Substring($start3, $len3)
}
