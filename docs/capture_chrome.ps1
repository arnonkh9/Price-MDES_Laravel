# Capture the largest visible Chrome window to a PNG file (ASCII-only to avoid PS5.1 encoding issues).
param(
    [Parameter(Mandatory=$true)][string]$OutPath,
    [int]$DelaySeconds = 1
)

Add-Type @"
using System;
using System.Runtime.InteropServices;
using System.Drawing;
using System.Drawing.Imaging;

public class Win {
    [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
    [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr h);
    [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
    [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, System.Text.StringBuilder s, int n);
    [DllImport("user32.dll")] public static extern bool EnumWindows(EnumWinDelegate d, IntPtr p);
    public delegate bool EnumWinDelegate(IntPtr h, IntPtr p);
    [DllImport("user32.dll")] public static extern bool ShowWindow(IntPtr h, int n);
    [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left,Top,Right,Bottom; }
}
"@ -ReferencedAssemblies System.Drawing,System.Windows.Forms

$script:cands = @()
$cb = [Win+EnumWinDelegate]{
    param($h, $p)
    if ([Win]::IsWindowVisible($h)) {
        $sb = New-Object Text.StringBuilder 256
        [Win]::GetWindowText($h, $sb, 256) | Out-Null
        $title = $sb.ToString()
        if ($title -match "Chrome") {
            $script:cands += @{ Handle = $h; Title = $title }
        }
    }
    return $true
}
[Win]::EnumWindows($cb, [IntPtr]::Zero) | Out-Null

if ($script:cands.Count -eq 0) { Write-Error "No Chrome window found"; exit 1 }

$best = $null
$bestArea = 0
foreach ($w in $script:cands) {
    $r = New-Object Win+RECT
    if ([Win]::GetWindowRect($w.Handle, [ref]$r)) {
        $area = ($r.Right - $r.Left) * ($r.Bottom - $r.Top)
        if ($area -gt $bestArea) { $bestArea = $area; $best = @{ Handle = $w.Handle; Title = $w.Title } }
    }
}
if (-not $best) { Write-Error "No measurable Chrome window"; exit 1 }

[Win]::ShowWindow($best.Handle, 9) | Out-Null
[Win]::SetForegroundWindow($best.Handle) | Out-Null
Start-Sleep -Seconds $DelaySeconds

$r = New-Object Win+RECT
[Win]::GetWindowRect($best.Handle, [ref]$r) | Out-Null
$w = $r.Right - $r.Left
$h = $r.Bottom - $r.Top
if ($w -le 0 -or $h -le 0) { Write-Error "Invalid window size: $w x $h"; exit 1 }

$bmp = New-Object System.Drawing.Bitmap $w, $h
$g   = [System.Drawing.Graphics]::FromImage($bmp)
$g.CopyFromScreen($r.Left, $r.Top, 0, 0, (New-Object System.Drawing.Size $w, $h))
$g.Dispose()

$dir = Split-Path -Parent $OutPath
if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Force -Path $dir | Out-Null }
$bmp.Save($OutPath, [System.Drawing.Imaging.ImageFormat]::Png)
$bmp.Dispose()

$kb = [math]::Round((Get-Item $OutPath).Length/1024,1)
Write-Output "OK: $w x $h saved to $OutPath ($kb KB)"
