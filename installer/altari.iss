; Installer Warung Altari POS. Dibangun lewat build.ps1 (jangan dikompilasi langsung
; sebelum folder dist\stage terisi).
#define AppName "Warung Altari POS"
#define AppVer "1.0.0"
#define Stage "dist\stage"

[Setup]
AppId={{6B3C2A4E-8F1D-4C7A-9E2B-A17A21A0F0C5}
AppName={#AppName}
AppVersion={#AppVer}
AppPublisher=Warung Altari
DefaultDirName={sd}\AltariPOS
DefaultGroupName=Warung Altari
DisableProgramGroupPage=yes
PrivilegesRequired=lowest
OutputDir=dist
OutputBaseFilename=AltariPOS-Setup
SetupIconFile={#Stage}\altari.ico
UninstallDisplayIcon={app}\altari.ico
Compression=lzma2/ultra64
SolidCompression=yes
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
WizardStyle=modern

[Tasks]
Name: "desktopicon"; Description: "Buat ikon di Desktop"
Name: "autostart"; Description: "Nyalakan server otomatis saat Windows menyala"

[Dirs]
Name: "{app}\backup"; Flags: uninsneveruninstall
Name: "{app}\logs"
Name: "{app}\tmp"

[Files]
Source: "{#Stage}\*"; Excludes: "\data\*"; DestDir: "{app}"; Flags: recursesubdirs createallsubdirs ignoreversion
; Database awal hanya dipasang sekali; instal ulang/update tidak menimpa data transaksi.
Source: "{#Stage}\data\*"; DestDir: "{app}\data"; Flags: recursesubdirs createallsubdirs uninsneveruninstall; Check: DataBelumAda

[Icons]
Name: "{autodesktop}\Warung Altari"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" mulai"; WorkingDir: "{app}"; IconFilename: "{app}\altari.ico"; Tasks: desktopicon
Name: "{autodesktop}\Backup Data Altari"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" backup"; WorkingDir: "{app}"; IconFilename: "{sys}\shell32.dll"; IconIndex: 258; Tasks: desktopicon
Name: "{group}\Warung Altari"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" mulai"; WorkingDir: "{app}"; IconFilename: "{app}\altari.ico"
Name: "{group}\Backup Data"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" backup"; WorkingDir: "{app}"; IconFilename: "{sys}\shell32.dll"; IconIndex: 258
Name: "{group}\Folder Backup"; Filename: "{app}\backup"
Name: "{group}\Matikan Server"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" berhenti"; WorkingDir: "{app}"; IconFilename: "{sys}\shell32.dll"; IconIndex: 27
Name: "{group}\Hapus Warung Altari"; Filename: "{uninstallexe}"
Name: "{userstartup}\Warung Altari (server)"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" latar"; WorkingDir: "{app}"; Tasks: autostart

[Run]
Filename: "{sys}\wscript.exe"; Parameters: """{app}\jalankan.vbs"" mulai"; Description: "Buka Warung Altari sekarang"; Flags: postinstall nowait skipifsilent

[UninstallRun]
Filename: "powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\AltariPOS.ps1"" berhenti"; Flags: runhidden waituntilterminated; RunOnceId: "MatikanAltari"

[UninstallDelete]
Type: filesandordirs; Name: "{app}\logs"
Type: filesandordirs; Name: "{app}\tmp"
Type: filesandordirs; Name: "{app}\app\storage"
Type: files; Name: "{app}\mysql\my.ini"
Type: files; Name: "{app}\php\php.ini"
Type: dirifempty; Name: "{app}\mysql"
Type: dirifempty; Name: "{app}\php"

[Code]
var
  SudahAdaData: Boolean;

function DataBelumAda: Boolean;
begin
  Result := not SudahAdaData;
end;

// Server lama dimatikan dulu agar file php/mysql tidak terkunci saat update.
function PrepareToInstall(var NeedsRestart: Boolean): String;
var
  Kode: Integer;
  Skrip: String;
begin
  SudahAdaData := DirExists(ExpandConstant('{app}\data\mysql'));
  Skrip := ExpandConstant('{app}\AltariPOS.ps1');
  if FileExists(Skrip) then
    Exec('powershell.exe', '-NoProfile -ExecutionPolicy Bypass -File "' + Skrip + '" berhenti', '', SW_HIDE, ewWaitUntilTerminated, Kode);
  Result := '';
end;
