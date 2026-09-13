@echo off
REM ==============================================================================
REM FOSO ERP WINDOWS TASK SCHEDULER BATCH RUNNER
REM ==============================================================================

set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php.exe

set SCRIPT_DIR=%~dp0
set RUNNER=%SCRIPT_DIR%cron_cli_runner.php

echo [%date% %time%] Running Foso ERP Automation Cron...
"%PHP_BIN%" "%RUNNER%" --action=all

echo [%date% %time%] Finished execution.
