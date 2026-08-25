Add-Type -AssemblyName System.Drawing
$imgPath = "C:\Users\anton\.gemini\antigravity-ide\brain\cae9a60e-6828-420b-a59b-dcbb07cf2a55\musicdown_logo_1787696738486.jpg"
$outIco = "c:\Users\anton\Downloads\phpdesktop-chrome-130.1-php-8.3\www\MusicDown\icon.ico"

$bmp = [System.Drawing.Image]::FromFile($imgPath)
$resized = New-Object System.Drawing.Bitmap(256, 256)
$g = [System.Drawing.Graphics]::FromImage($resized)
$g.DrawImage($bmp, 0, 0, 256, 256)
$g.Dispose()

$ms = New-Object System.IO.MemoryStream
$resized.Save($ms, [System.Drawing.Imaging.ImageFormat]::Png)
$pngBytes = $ms.ToArray()
$ms.Dispose()
$bmp.Dispose()
$resized.Dispose()

$fs = [System.IO.File]::Create($outIco)
$writer = New-Object System.IO.BinaryWriter($fs)
$writer.Write([uint16]0)
$writer.Write([uint16]1)
$writer.Write([uint16]1)
$writer.Write([byte]0)
$writer.Write([byte]0)
$writer.Write([byte]0)
$writer.Write([byte]0)
$writer.Write([uint16]1)
$writer.Write([uint16]32)
$writer.Write([uint32]$pngBytes.Length)
$writer.Write([uint32]22)
$writer.Write($pngBytes)
$writer.Close()
$fs.Close()
Write-Host "Icon generated successfully!"
