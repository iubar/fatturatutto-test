@echo off
SET CONEMU_EXE="%MY_APPS%\ConEmuPack.150813g\ConEmu64.exe"
%CONEMU_EXE% -single -run "cd %~dp0 & robo run"