@echo off

FOR %%G IN (%*) DO call :isSet %%G
set prompt=[%rdevenv%]$_$p$g
GOTO :eof

:isSet
(@echo %rdevenv%|findstr /i %1 > NUL)
if %errorlevel% equ 1 call :%1
GOTO :eof

:gradle
call :updateDevEnv gradle
set PATH=%PATH%;"C:\work\opt\gradle-3.3\bin"
GOTO :eof

:java
call :updateDevEnv java
set PATH=%PATH%;"C:\Program Files\Java\jdk1.8.0_121\bin"
GOTO :eof

:python
call :updateDevEnv python
set PATH=%PATH%;"C:\Python27"
GOTO :eof

:updateDevEnv
if "%rdevenv%"=="" (set rdevenv=%1) else (set rdevenv=%rdevenv% %1)
GOTO :eof