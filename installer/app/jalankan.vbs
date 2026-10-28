' Menjalankan AltariPOS.ps1 tanpa memunculkan jendela PowerShell.
Set sh = CreateObject("WScript.Shell")
folder = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)
aksi = "mulai"
If WScript.Arguments.Count > 0 Then aksi = WScript.Arguments(0)
sh.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & folder & "\AltariPOS.ps1"" " & aksi, 0, False
