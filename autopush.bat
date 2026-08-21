@echo off
title Git Auto-Push Watcher
echo Starting Git Auto-Push Watcher...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0autopush.ps1" %*
