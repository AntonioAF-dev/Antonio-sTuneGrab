Add-Type -AssemblyName System.Windows.Forms
$form = New-Object System.Windows.Forms.Form
$form.TopMost = $true
$dialog = New-Object System.Windows.Forms.FolderBrowserDialog
$dialog.Description = "Selecciona la carpeta de destino"
$dialog.ShowNewFolderButton = $true
$result = $dialog.ShowDialog($form)
if ($result -eq [System.Windows.Forms.DialogResult]::OK) {
    Write-Output $dialog.SelectedPath
}
