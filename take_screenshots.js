const puppeteer = require('../Practical_3/node_modules/puppeteer-core');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

async function runScreenshots() {
    const phpExe = 'C:\\xampp\\php\\php.exe';
    const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
    const port = 8089;

    if (!fs.existsSync(phpExe)) {
        console.error("PHP executable not found at: " + phpExe);
        return;
    }

    const screenshotDir = path.join(__dirname, 'screenshots');
    if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
    }

    console.log(`[1/8] Starting PHP built-in server on http://localhost:${port}...`);
    const phpServer = spawn(phpExe, ['-S', `localhost:${port}`], { cwd: __dirname });

    // Wait for PHP server to initialize
    await new Promise(r => setTimeout(r, 1500));

    console.log("[2/8] Launching Edge browser via Puppeteer...");
    let browser;
    const tempProfile = path.join(__dirname, '.temp_browser_profile');
    try {
        browser = await puppeteer.launch({
            executablePath: edgePath,
            headless: true,
            userDataDir: tempProfile,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    } catch(err) {
        console.log("Retrying with Chrome executable...");
        const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
        browser = await puppeteer.launch({
            executablePath: chromePath,
            headless: true,
            userDataDir: tempProfile,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    }

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 950, deviceScaleFactor: 2 });

    const baseUrl = `http://localhost:${port}/register.php`;

    try {
        // --- SCREENSHOT 1: Clean Registration Form ---
        console.log("[3/8] Capturing 1_registration_form_clean.png...");
        await page.goto(baseUrl, { waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '1_registration_form_clean.png'), fullPage: true });

        // --- SCREENSHOT 2: Empty Field Validation ---
        console.log("[4/8] Capturing 2_empty_field_validation.png...");
        await page.click('#submitBtn');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '2_empty_field_validation.png'), fullPage: true });

        // --- SCREENSHOT 3: Invalid Email Validation ---
        console.log("[5/8] Capturing 3_invalid_email_validation.png...");
        await page.goto(baseUrl, { waitUntil: 'networkidle0' });
        await page.type('#full_name', 'Rohan Shah');
        await page.type('#username', 'rohan_s');
        await page.type('#email', 'invalid-email-format');
        await page.type('#password', 'SecurePass@123');
        await page.type('#confirm_password', 'SecurePass@123');
        await page.click('#submitBtn');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '3_invalid_email_validation.png'), fullPage: true });

        // --- SCREENSHOT 4: Password Mismatch Validation ---
        console.log("[6/8] Capturing 4_password_mismatch_validation.png...");
        await page.goto(baseUrl, { waitUntil: 'networkidle0' });
        await page.type('#full_name', 'Rohan Shah');
        await page.type('#username', 'rohan_s');
        await page.type('#email', 'rohan.shah@charusat.ac.in');
        await page.type('#password', 'Password@123');
        await page.type('#confirm_password', 'DifferentPassword@999');
        await page.click('#submitBtn');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '4_password_mismatch_validation.png'), fullPage: true });

        // --- SCREENSHOT 5: Duplicate Email / Username Validation ---
        console.log("[7/8] Capturing 5_duplicate_username_email_validation.png...");
        await page.goto(baseUrl, { waitUntil: 'networkidle0' });
        // Use already existing username 'krisha_patel'
        await page.type('#full_name', 'Duplicate User Test');
        await page.type('#username', 'krisha_patel');
        await page.type('#email', 'krisha.patel@charusat.ac.in');
        await page.type('#password', 'MyPassword@123');
        await page.type('#confirm_password', 'MyPassword@123');
        await page.click('#submitBtn');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '5_duplicate_username_email_validation.png'), fullPage: true });

        // --- SCREENSHOT 6: Successful User Registration & Database Insertion ---
        console.log("[8/8] Capturing 6_successful_registration_and_hash.png...");
        await page.goto(baseUrl, { waitUntil: 'networkidle0' });
        await page.type('#full_name', 'Devansh Mehta');
        await page.type('#username', 'devansh_m');
        await page.type('#email', 'devansh.mehta@charusat.ac.in');
        await page.type('#password', 'Student@CSPIT2026');
        await page.type('#confirm_password', 'Student@CSPIT2026');
        await page.click('#submitBtn');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.screenshot({ path: path.join(screenshotDir, '6_successful_registration_and_hash.png'), fullPage: true });

        console.log("All Practical 9 screenshots captured successfully in high resolution!");
    } catch (err) {
        console.error("Error capturing screenshots:", err);
    } finally {
        if (browser) await browser.close();
        if (phpServer) phpServer.kill();
    }
}

runScreenshots();
