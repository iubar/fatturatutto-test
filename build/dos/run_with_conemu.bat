@echo off
SET CONEMU_EXE="%MY_APPS%\ConEmuPack.150813g\ConEmu64.exe"
SET CONEMUC_EXE="%MY_APPS%\ConEmuPack.150813g\ConEmu\ConEmuC64.exe"
%CONEMU_EXE% -run "%~dp0run.bat"

