Set objShell = CreateObject("Shell.Application")
Set objFolder = objShell.BrowseForFolder(0, "Selecciona la carpeta de destino", 0, 17)
If Not objFolder Is Nothing Then
    Wscript.Echo objFolder.Self.Path
End If
