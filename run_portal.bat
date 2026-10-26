@echo off
title StudentHub Portal - Practical 9 (WDF)
echo ======================================================================
echo  StudentHub Portal: Practical 9 - User Registration & Bcrypt Hashing
echo  CHARUSAT - CSPIT | Web Development Frameworks (WDF)
echo ======================================================================
echo.
echo Starting local PHP development server on http://localhost:8000 ...
start "" "http://localhost:8000/register.php"
"C:\xampp\php\php.exe" -S localhost:8000
pause
