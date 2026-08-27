@echo off
title HTTPS Tunnel for Mobile Testing
echo.
echo  ============================================
echo   STARTING HTTPS TUNNEL FOR MOBILE TESTING
echo  ============================================
echo.
echo   This creates a public HTTPS URL for your app.
echo   The mic (Speak and Repeat quiz) only works on HTTPS.
echo.
echo   Your URL will appear below (starts with https://)...
echo.
echo   --------------------------------------------
echo.

cd /d c:\xampp\htdocs\kid
npx --yes localtunnel --port 8081

echo.
echo   --------------------------------------------
echo   Tunnel stopped. Close this window when done.
pause