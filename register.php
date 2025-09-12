<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <title>Jeepney Tracking — Create account</title>
    <link rel="stylesheet" href="src/output.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Fallback SweetAlert styles if CDN fails -->
    <style id="swal-fallback" style="display: none;">
        .swal-fallback {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            z-index: 10000;
            text-align: center;
            max-width: 400px;
            border: 1px solid #e5e7eb;
        }
        .swal-fallback h2 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 18px;
        }
        .swal-fallback p {
            margin: 0 0 20px 0;
            color: #6b7280;
        }
        .swal-fallback button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .swal-fallback-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        .swal-fallback.success h2 { color: #059669; }
        .swal-fallback.error h2 { color: #dc2626; }
        .swal-fallback.success button { background: #059669; }
        .swal-fallback.error button { background: #dc2626; }
    </style>
    <style>
        /* Custom styles for validation indicators */
        .input-valid {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2322c55e' viewBox='0 0 16 16'%3E%3Cpath d='M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.75.75 0 0 1 1.06-1.06L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            padding-right: 36px !important;
        }

        /* Fix for select elements to prevent checkmark overlap */
        select.input-valid {
            background-position: right 36px center;
        }

        /* Password strength meter */
        .password-strength-meter {
            height: 4px;
            width: 100%;
            border-radius: 2px;
            margin-top: 6px;
            transition: all 0.3s ease;
            background: #e5e7eb;
        }

        .password-strength-meter div {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            width: 25%;
            background-color: #ef4444;
        }

        .strength-fair {
            width: 50%;
            background-color: #f97316;
        }

        .strength-good {
            width: 75%;
            background-color: #facc15;
        }

        .strength-strong {
            width: 100%;
            background-color: #22c55e;
        }

        /* Loading spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top-color: #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-left: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .dark .spinner {
            border-color: rgba(255, 255, 255, 0.1);
            border-top-color: #38bdf8;
        }

        /* Address preview */
        .address-preview {
            margin-top: 8px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .dark .address-preview {
            background-color: rgba(30, 41, 59, 0.7);
            border-color: #334155;
        }
    </style>

    <!-- Add this to the <style> section in the head of register.php -->
    <style>
        /* Other styles remain unchanged */

        /* Force dropdown options to have black text */
        select option {
            color: black !important;
            background-color: white !important;
        }

        /* Style the selected/highlighted option */
        select option:checked,
        select option:hover,
        select option:focus {
            background-color: #0d6efd !important;
            color: white !important;
        }

        /* Ensure dropdowns have proper styling in dark mode */
        .dark select {
            color: white;
            background-color: rgba(15, 23, 42, 0.8);
        }

        /* But their options should still be black on white */
        .dark select option {
            color: black !important;
            background-color: white !important;
        }
    </style>
</head>

<body
    class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 bg-[radial-gradient(30%_40%_at_80%_0%,rgba(56,189,248,0.12),transparent_60%),radial-gradient(25%_35%_at_10%_10%,rgba(34,197,94,0.08),transparent_60%)]">
    <main class="min-h-svh grid place-items-center p-6">
        <section
            class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/70 p-6 shadow-xl backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 md:p-8"
            role="region" aria-labelledby="signup-title">
            <div class="mb-4 flex items-center justify-center gap-3 select-none">
                <div
                    class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 bg-gradient-to-b from-sky-200/40 to-transparent text-[11px] font-bold tracking-wide dark:border-slate-800 dark:from-sky-400/20">
                    JT</div>
                <div class="text-sm font-bold tracking-wide">Jeepney GPS Tracking</div>
            </div>

            <header class="mb-5">
                <h1 id="signup-title" class="flex items-center gap-2 text-xl font-bold">
                    <span
                        class="inline-block h-2 w-2 rounded-full bg-sky-500 shadow-[0_0_0_6px_rgba(14,165,233,0.12)]"></span>
                    Create account
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Start tracking rides with your new account.
                </p>
            </header>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                    <?php echo $_SESSION['register_error'];
                    unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>

            <form id="register-form" action="backend/register.php" method="POST" class="grid gap-4" novalidate>
                <div class="grid grid-cols-2 gap-3">
                    <div id="f-firstname" class="grid gap-2">
                        <label for="firstname" class="text-[13px] font-semibold">First Name</label>
                        <input id="firstname" name="firstname" type="text" placeholder="Juan" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <p id="firstname-error" class="hidden text-xs text-red-500">Enter your first name.</p>
                    </div>

                    <div id="f-lastname" class="grid gap-2">
                        <label for="lastname" class="text-[13px] font-semibold">Last Name</label>
                        <input id="lastname" name="lastname" type="text" placeholder="Dela Cruz" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <p id="lastname-error" class="hidden text-xs text-red-500">Enter your last name.</p>
                    </div>
                </div>

                <div id="f-username" class="grid gap-2">
                    <label for="username" class="text-[13px] font-semibold">Username</label>
                    <input id="username" name="username" type="text" placeholder="jeepfan_01" required
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                    <p id="username-error" class="hidden text-xs text-red-500">Use 3+ letters, numbers, or underscores.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div id="f-email" class="grid gap-2">
                        <label for="email" class="text-[13px] font-semibold">Email Address</label>
                        <input id="email" name="email" type="email" placeholder="juan@example.com" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <p id="email-error" class="hidden text-xs text-red-500">Please enter a valid email address.</p>
                    </div>

                    <div id="f-phone" class="grid gap-2">
                        <label for="phone" class="text-[13px] font-semibold">Phone Number</label>
                        <input id="phone" name="phone" type="tel" placeholder="09123456789" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <p id="phone-error" class="hidden text-xs text-red-500">Please enter a valid phone number.</p>
                    </div>
                </div>

                <!-- Location dropdowns with loading indicators -->
                <div id="f-region" class="grid gap-2">
                    <label for="region" class="text-[13px] font-semibold flex items-center">
                        Region <span id="region-loading" class=" ml-2 hidden"></span>
                    </label>
                    <select id="region" name="region" required
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20">
                        <option value="">Select Region</option>
                        <!-- Will be populated by API -->
                    </select>
                    <p id="region-error" class="hidden text-xs text-red-500">Please select a region.</p>
                </div>

                <div id="f-municipality" class="grid gap-2">
                    <label for="municipality" class="text-[13px] font-semibold flex items-center">
                        Province <span id="municipality-loading" class="ml-2 hidden"></span>
                    </label>
                    <select id="municipality" name="province_code" required disabled
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20">
                        <option value="">Select Province</option>
                        <!-- Will be populated based on region selection -->
                    </select>
                    <p id="municipality-error" class="hidden text-xs text-red-500">Please select a province.</p>
                </div>

                <div id="f-barangay" class="grid gap-2">
                    <label for="barangay" class="text-[13px] font-semibold flex items-center">
                        Municipality <span id="barangay-loading" class="ml-2 hidden"></span>
                    </label>
                    <select id="barangay" name="municipality_code" required disabled
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20">
                        <option value="">Select Municipality</option>
                        <!-- Will be populated based on province selection -->
                    </select>
                    <p id="barangay-error" class="hidden text-xs text-red-500">Please select a municipality.</p>
                </div>

                <!-- Barangay dropdown -->
                <div id="f-barangay-select" class="grid gap-2">
                    <label for="barangay-select" class="text-[13px] font-semibold flex items-center">
                        Barangay <span id="barangay-select-loading" class="ml-2 hidden"></span>
                    </label>
                    <select id="barangay-select" name="barangay_code" required disabled
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20">
                        <option value="">Select Barangay</option>
                    </select>
                    <p id="barangay-select-error" class="hidden text-xs text-red-500">Please select a barangay.</p>
                </div>

                <!-- Address preview -->
                <div id="address-preview-container" class="hidden">
                    <div class="address-preview">
                        <div class="text-[13px] font-semibold mb-1 text-dark">Address Preview:</div>
                        <div id="address-preview-text" class="text-slate-600 dark:text-dark"></div>
                    </div>
                </div>

                <div id="f-street" class="grid gap-2">
                    <label for="street" class="text-[13px] font-semibold">Street Address</label>
                    <input id="street" name="street" type="text" placeholder="123 Main St." required
                        class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                    <p id="street-error" class="hidden text-xs text-red-500">Please enter your street address.</p>
                </div>

                <div id="f-password" class="grid gap-2">
                    <label for="password" class="text-[13px] font-semibold">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="new-password"
                            placeholder="••••••••" minlength="6" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 pr-12 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <button id="toggle-pass" type="button" aria-label="Show password" aria-pressed="false"
                            class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white/70 text-slate-500 transition hover:text-slate-900 dark:border-slate-800 dark:bg-slate-800/60 dark:hover:text-slate-100">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="opacity-80">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor"
                                    stroke-width="1.6" />
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                    <!-- Password strength meter -->
                    <div class="password-strength-meter">
                        <div id="password-strength-bar"></div>
                    </div>
                    <div id="password-strength-text" class="text-xs text-slate-500"></div>
                    <p id="password-error" class="hidden text-xs text-red-500">Password must be at least 6 characters.
                    </p>
                </div>

                <div id="f-confirm" class="grid gap-2">
                    <label for="confirm" class="text-[13px] font-semibold">Confirm password</label>
                    <div class="relative">
                        <input id="confirm" name="confirm" type="password" autocomplete="new-password"
                            placeholder="••••••••" minlength="6" required
                            class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 pr-12 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
                        <button id="toggle-confirm" type="button" aria-label="Show password" aria-pressed="false"
                            class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white/70 text-slate-500 transition hover:text-slate-900 dark:border-slate-800 dark:bg-slate-800/60 dark:hover:text-slate-100">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="opacity-80">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor"
                                    stroke-width="1.6" />
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" />
                            </svg>
                        </button>
                    </div>
                    <p id="confirm-error" class="hidden text-xs text-red-500">Passwords do not match.</p>
                </div>

                <div class="mt-1 flex items-center justify-between">
                    <label
                        class="inline-flex select-none items-center gap-2 text-[13px] text-slate-500 dark:text-slate-400">
                        <input id="terms" name="terms" type="checkbox" class="peer" required />
                        I agree to the Terms and Privacy
                    </label>
                    <p id="terms-error" class="hidden text-xs text-red-500">Please accept the terms.</p>
                </div>

                <div class="grid gap-3">
                    <button id="submit-btn" type="submit"
                        class="btn-primary inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-[0_8px_20px_rgba(2,6,23,0.12)] transition hover:shadow-[0_10px_26px_rgba(2,6,23,0.18)] active:translate-y-px dark:bg-white dark:text-slate-950">Create
                        account</button>
                    <div
                        class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-px bg-slate-200 dark:bg-slate-800"></span>
                        <span>or</span>
                        <span class="h-px bg-slate-200 dark:bg-slate-800"></span>
                    </div>
                    <a href="login.php"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-center text-sm font-semibold text-slate-900 transition hover:bg-white dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:hover:bg-slate-900/40">Already
                        have an account? Sign in</a>
                </div>
            </form>

            <p class="mt-5 text-center text-[13px] text-slate-500 dark:text-slate-400">By creating an account, you agree
                to our Terms and acknowledge our Privacy Policy.</p>
        </section>
    </main>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Fallback SweetAlert implementation -->
    <script>
        // Fallback Swal implementation if CDN fails
        if (typeof Swal === 'undefined') {
            window.Swal = {
                fire: function(options) {
                    return new Promise((resolve) => {
                        let title, text, icon;
                        
                        if (typeof options === 'string') {
                            title = arguments[0];
                            text = arguments[1];
                            icon = arguments[2];
                        } else {
                            title = options.title;
                            text = options.text || options.html;
                            icon = options.icon;
                        }
                        
                        // Create overlay
                        const overlay = document.createElement('div');
                        overlay.className = 'swal-fallback-overlay';
                        
                        // Create modal
                        const modal = document.createElement('div');
                        modal.className = `swal-fallback ${icon}`;
                        
                        const titleEl = document.createElement('h2');
                        titleEl.textContent = title;
                        
                        const textEl = document.createElement('p');
                        textEl.innerHTML = text;
                        
                        const button = document.createElement('button');
                        button.textContent = 'OK';
                        button.onclick = () => {
                            document.body.removeChild(overlay);
                            resolve({ isConfirmed: true });
                        };
                        
                        modal.appendChild(titleEl);
                        modal.appendChild(textEl);
                        modal.appendChild(button);
                        
                        overlay.appendChild(modal);
                        document.body.appendChild(overlay);
                        
                        // Auto-close after 5 seconds
                        setTimeout(() => {
                            if (overlay.parentNode) {
                                document.body.removeChild(overlay);
                                resolve({ isConfirmed: true });
                            }
                        }, 5000);
                    });
                },
                showLoading: function() {
                    console.log('Loading...');
                }
            };
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('register-form');
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const region = document.getElementById('region');
            const municipality = document.getElementById('municipality');
            const barangay = document.getElementById('barangay');
            const barangaySelect = document.getElementById('barangay-select');
            const street = document.getElementById('street');
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm');
            const terms = document.getElementById('terms');

            const firstnameErr = document.getElementById('firstname-error');
            const lastnameErr = document.getElementById('lastname-error');
            const userErr = document.getElementById('username-error');
            const emailErr = document.getElementById('email-error');
            const phoneErr = document.getElementById('phone-error');
            const regionErr = document.getElementById('region-error');
            const municipalityErr = document.getElementById('municipality-error');
            const barangayErr = document.getElementById('barangay-error');
            const barangaySelectErr = document.getElementById('barangay-select-error');
            const streetErr = document.getElementById('street-error');
            const pwErr = document.getElementById('password-error');
            const confErr = document.getElementById('confirm-error');
            const termsErr = document.getElementById('terms-error');

            const togglePass = document.getElementById('toggle-pass');
            const toggleConfirm = document.getElementById('toggle-confirm');
            const submitBtn = document.getElementById('submit-btn');

            const passwordStrengthBar = document.getElementById('password-strength-bar');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const addressPreviewContainer = document.getElementById('address-preview-container');
            const addressPreviewText = document.getElementById('address-preview-text');

            // Validation indicator function
            function showValidStatus(inputEl, isValid) {
                if (isValid) {
                    inputEl.classList.add('input-valid');
                } else {
                    inputEl.classList.remove('input-valid');
                }
            }

            // Basic validation
            function validateInput(input, errorEl, condition, errorMsg) {
                const isValid = condition(input.value);
                showError(input, errorEl, !isValid);
                showValidStatus(input, isValid);
                return isValid;
            }

            function showError(inputEl, msgEl, show) {
                inputEl.classList.toggle('ring-4', show);
                inputEl.classList.toggle('ring-red-300/40', show);
                inputEl.classList.toggle('border-red-400', show);
                msgEl.classList.toggle('hidden', !show);
                inputEl.setAttribute('aria-invalid', show ? 'true' : 'false');
            }

            // Password strength checker
            function checkPasswordStrength(password) {
                let score = 0;

                // Length check
                if (password.length >= 8) score += 1;
                if (password.length >= 10) score += 1;

                // Complexity checks
                if (/[A-Z]/.test(password)) score += 1;
                if (/[a-z]/.test(password)) score += 1;
                if (/[0-9]/.test(password)) score += 1;
                if (/[^A-Za-z0-9]/.test(password)) score += 1;

                // Calculate percentage
                const percent = Math.min(Math.floor(score / 6 * 100), 100);

                // Return score level and percent
                if (percent <= 25) return { level: 'weak', percent };
                if (percent <= 50) return { level: 'fair', percent };
                if (percent <= 75) return { level: 'good', percent };
                return { level: 'strong', percent };
            }

            // Update password strength indicator
            function updatePasswordStrength(password) {
                if (password.length === 0) {
                    passwordStrengthBar.className = '';
                    passwordStrengthBar.style.width = '0';
                    passwordStrengthText.textContent = '';
                    return;
                }

                const strength = checkPasswordStrength(password);

                // Remove all classes and add the appropriate one
                passwordStrengthBar.className = '';
                passwordStrengthBar.classList.add(`strength-${strength.level}`);

                // Update text
                const strengthTexts = {
                    weak: 'Weak - Add more characters and symbols',
                    fair: 'Fair - Try adding numbers or symbols',
                    good: 'Good - Consider adding special characters',
                    strong: 'Strong - Excellent password!'
                };

                passwordStrengthText.textContent = strengthTexts[strength.level];

                // Set color based on strength
                const colors = {
                    weak: 'text-red-500',
                    fair: 'text-orange-500',
                    good: 'text-yellow-500',
                    strong: 'text-green-500'
                };

                // Remove all color classes
                passwordStrengthText.className = 'text-xs';
                passwordStrengthText.classList.add(colors[strength.level]);
            }

            // Update the address preview
            function updateAddressPreview() {
                const regionText = region.options[region.selectedIndex]?.text || '';
                const provinceText = municipality.options[municipality.selectedIndex]?.text || '';
                const municipalityText = barangay.options[barangay.selectedIndex]?.text || '';

                let barangayText = '';
                if (barangaySelect && barangaySelect.selectedIndex > 0) {
                    barangayText = barangaySelect.options[barangaySelect.selectedIndex].text;
                }

                const streetText = street.value.trim();

                // Only show preview if at least one location field is selected
                if (regionText && regionText !== 'Select Region') {
                    let previewText = '';

                    if (streetText) previewText += streetText + ', ';
                    if (barangayText && barangayText !== 'Select Barangay') previewText += barangayText + ', ';
                    if (municipalityText && municipalityText !== 'Select Municipality') previewText += municipalityText + ', ';
                    if (provinceText && provinceText !== 'Select Province') previewText += provinceText + ', ';
                    if (regionText) previewText += regionText;

                    addressPreviewText.textContent = previewText;
                    addressPreviewContainer.classList.remove('hidden');
                } else {
                    addressPreviewContainer.classList.add('hidden');
                }
            }

            // Function to fetch regions
            async function fetchRegions() {
                const regionLoading = document.getElementById('region-loading');
                regionLoading.classList.add('spinner');
                regionLoading.classList.remove('hidden');

                try {
                    const response = await fetch('https://psgc.gitlab.io/api/regions/');
                    if (!response.ok) throw new Error('Failed to fetch regions');

                    const regions = await response.json();

                    regions.sort((a, b) => a.name.localeCompare(b.name)).forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.code;
                        option.textContent = region.name;
                        document.getElementById('region').appendChild(option);
                    });
                } catch (error) {
                    console.error('Error fetching regions:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Could not load location data. Please try again later.',
                        icon: 'error'
                    });
                } finally {
                    regionLoading.classList.remove('spinner');
                    regionLoading.classList.add('hidden');
                }
            }

            // Function to fetch provinces/cities by region code
            async function fetchProvinces(regionCode) {
                const municipalityLoading = document.getElementById('municipality-loading');
                municipalityLoading.classList.add('spinner');
                municipalityLoading.classList.remove('hidden');
                municipality.disabled = true;
                barangay.disabled = true;
                barangay.innerHTML = '<option value="">Select Municipality</option>';

                if (barangaySelect) {
                    barangaySelect.disabled = true;
                    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                }

                try {
                    const response = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`);
                    if (!response.ok) throw new Error('Failed to fetch provinces');

                    const provinces = await response.json();

                    municipality.innerHTML = '<option value="">Select Province</option>';

                    provinces.sort((a, b) => a.name.localeCompare(b.name)).forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        municipality.appendChild(option);
                    });

                    municipality.disabled = false;
                    updateAddressPreview();
                } catch (error) {
                    console.error('Error fetching provinces:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Could not load province data. Please try again later.',
                        icon: 'error'
                    });
                } finally {
                    municipalityLoading.classList.remove('spinner');
                    municipalityLoading.classList.add('hidden');
                }
            }

            // Function to fetch municipalities by province code
            async function fetchMunicipalities(provinceCode) {
                const barangayLoading = document.getElementById('barangay-loading');
                barangayLoading.classList.add('spinner');
                barangayLoading.classList.remove('hidden');
                barangay.disabled = true;

                if (barangaySelect) {
                    barangaySelect.disabled = true;
                    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                }

                try {
                    const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/municipalities/`);
                    if (!response.ok) throw new Error('Failed to fetch municipalities');

                    const municipalities = await response.json();

                    barangay.innerHTML = '<option value="">Select Municipality</option>';

                    municipalities.sort((a, b) => a.name.localeCompare(b.name)).forEach(muni => {
                        const option = document.createElement('option');
                        option.value = muni.code;
                        option.textContent = muni.name;
                        barangay.appendChild(option);
                    });

                    barangay.disabled = false;
                    updateAddressPreview();
                } catch (error) {
                    console.error('Error fetching municipalities:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Could not load municipality data. Please try again later.',
                        icon: 'error'
                    });
                } finally {
                    barangayLoading.classList.remove('spinner');
                    barangayLoading.classList.add('hidden');
                }
            }

            // Function to fetch barangays by municipality code
            async function fetchBarangays(municipalityCode) {
                const barangaySelectLoading = document.getElementById('barangay-select-loading');
                if (barangaySelectLoading) {
                    barangaySelectLoading.classList.add('spinner');
                    barangaySelectLoading.classList.remove('hidden');
                }

                if (barangaySelect) {
                    barangaySelect.disabled = true;
                    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                }

                try {
                    const response = await fetch(`https://psgc.gitlab.io/api/municipalities/${municipalityCode}/barangays/`);
                    if (!response.ok) throw new Error('Failed to fetch barangays');

                    const barangays = await response.json();

                    if (barangaySelect) {
                        barangays.sort((a, b) => a.name.localeCompare(b.name)).forEach(brgy => {
                            const option = document.createElement('option');
                            option.value = brgy.code;
                            option.textContent = brgy.name;
                            barangaySelect.appendChild(option);
                        });

                        barangaySelect.disabled = false;
                    }

                    updateAddressPreview();
                } catch (error) {
                    console.error('Error fetching barangays:', error);
                    if (error.message !== 'Failed to fetch barangays: 404') {
                        Swal.fire({
                            title: 'Error',
                            text: 'Could not load barangay data. Please try again later.',
                            icon: 'error'
                        });
                    }
                } finally {
                    if (barangaySelectLoading) {
                        barangaySelectLoading.classList.remove('spinner');
                        barangaySelectLoading.classList.add('hidden');
                    }
                }
            }

            // Initialize the location dropdowns
            fetchRegions();

            // Full validation function
            window.validate = function () {
                const firstnameValid = validateInput(
                    firstname,
                    firstnameErr,
                    (value) => value.trim().length >= 2,
                    "Please enter your first name"
                );

                const lastnameValid = validateInput(
                    lastname,
                    lastnameErr,
                    (value) => value.trim().length >= 2,
                    "Please enter your last name"
                );

                const usernameValid = validateInput(
                    username,
                    userErr,
                    (value) => /^[A-Za-z0-9_]{3,}$/.test(value.trim()),
                    "Use 3+ letters, numbers, or underscores"
                );

                const emailValid = validateInput(
                    email,
                    emailErr,
                    (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim()),
                    "Please enter a valid email address"
                );

                const phoneValid = validateInput(
                    phone,
                    phoneErr,
                    (value) => /^(\+63|0)\d{10}$/.test(value.replace(/\s/g, '')),
                    "Please enter a valid phone number (e.g., 09123456789)"
                );

                const regionValid = validateInput(
                    region,
                    regionErr,
                    (value) => value !== '',
                    "Please select a region"
                );

                const municipalityValid = validateInput(
                    municipality,
                    municipalityErr,
                    (value) => value !== '',
                    "Please select a province"
                );

                const barangayValid = validateInput(
                    barangay,
                    barangayErr,
                    (value) => value !== '',
                    "Please select a municipality"
                );

                let barangaySelectValid = true;
                if (barangaySelect && !barangaySelect.disabled) {
                    barangaySelectValid = validateInput(
                        barangaySelect,
                        barangaySelectErr,
                        (value) => value !== '',
                        "Please select a barangay"
                    );
                }

                const streetValid = validateInput(
                    street,
                    streetErr,
                    (value) => value.trim().length >= 3,
                    "Please enter your street address"
                );

                const passwordValid = validateInput(
                    password,
                    pwErr,
                    (value) => value.length >= 6,
                    "Password must be at least 6 characters"
                );

                const confirmValid = validateInput(
                    confirm,
                    confErr,
                    (value) => value === password.value && value.length >= 6,
                    "Passwords do not match"
                );

                const termsValid = terms.checked;
                termsErr.classList.toggle('hidden', termsValid);

                return firstnameValid && lastnameValid && usernameValid && emailValid && phoneValid &&
                    regionValid && municipalityValid && barangayValid && barangaySelectValid &&
                    streetValid && passwordValid && confirmValid && termsValid;
            };

            // Event listeners
            togglePass.addEventListener('click', () => {
                const isPass = password.type === 'password';
                password.type = isPass ? 'text' : 'password';
                togglePass.setAttribute('aria-pressed', isPass ? 'true' : 'false');
                togglePass.setAttribute('aria-label', isPass ? 'Hide password' : 'Show password');
                password.focus({ preventScroll: true });
            });

            toggleConfirm.addEventListener('click', () => {
                const isPass = confirm.type === 'password';
                confirm.type = isPass ? 'text' : 'password';
                toggleConfirm.setAttribute('aria-pressed', isPass ? 'true' : 'false');
                toggleConfirm.setAttribute('aria-label', isPass ? 'Hide password' : 'Show password');
                confirm.focus({ preventScroll: true });
            });

            // Update validation status on input
            firstname.addEventListener('input', () => {
                showError(firstname, firstnameErr, false);
                validateInput(
                    firstname,
                    firstnameErr,
                    (value) => value.trim().length >= 2,
                    "Please enter your first name"
                );
            });

            lastname.addEventListener('input', () => {
                showError(lastname, lastnameErr, false);
                validateInput(
                    lastname,
                    lastnameErr,
                    (value) => value.trim().length >= 2,
                    "Please enter your last name"
                );
            });

            username.addEventListener('input', () => {
                showError(username, userErr, false);
                validateInput(
                    username,
                    userErr,
                    (value) => /^[A-Za-z0-9_]{3,}$/.test(value.trim()),
                    "Use 3+ letters, numbers, or underscores"
                );
            });

            email.addEventListener('input', () => {
                showError(email, emailErr, false);
                validateInput(
                    email,
                    emailErr,
                    (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim()),
                    "Please enter a valid email address"
                );
            });

            phone.addEventListener('input', () => {
                showError(phone, phoneErr, false);
                validateInput(
                    phone,
                    phoneErr,
                    (value) => /^(\+63|0)\d{10}$/.test(value.replace(/\s/g, '')),
                    "Please enter a valid phone number (e.g., 09123456789)"
                );
            });

            street.addEventListener('input', () => {
                showError(street, streetErr, false);
                validateInput(
                    street,
                    streetErr,
                    (value) => value.trim().length >= 3,
                    "Please enter your street address"
                );
                updateAddressPreview();
            });

            password.addEventListener('input', () => {
                showError(password, pwErr, false);
                validateInput(
                    password,
                    pwErr,
                    (value) => value.length >= 6,
                    "Password must be at least 6 characters"
                );
                updatePasswordStrength(password.value);
            });

            confirm.addEventListener('input', () => {
                showError(confirm, confErr, false);
                validateInput(
                    confirm,
                    confErr,
                    (value) => value === password.value && value.length >= 6,
                    "Passwords do not match"
                );
            });

            terms.addEventListener('change', () => {
                termsErr.classList.toggle('hidden', terms.checked);
            });

            // Address dropdowns event listeners
            region.addEventListener('change', function () {
                showError(region, regionErr, false);
                showValidStatus(region, region.value !== '');
                fetchProvinces(this.value);
                updateAddressPreview();
            });

            municipality.addEventListener('change', function () {
                showError(municipality, municipalityErr, false);
                showValidStatus(municipality, municipality.value !== '');
                fetchMunicipalities(this.value);
                updateAddressPreview();
            });

            barangay.addEventListener('change', function () {
                showError(barangay, barangayErr, false);
                showValidStatus(barangay, barangay.value !== '');
                fetchBarangays(this.value);
                updateAddressPreview();
            });

            if (barangaySelect) {
                barangaySelect.addEventListener('change', function () {
                    showError(barangaySelect, barangaySelectErr, false);
                    showValidStatus(barangaySelect, barangaySelect.value !== '');
                    updateAddressPreview();
                });
            }

            // Form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!validate()) {
                    return false;
                }

                // Get form data
                const formData = new FormData(form);

                // Create full address
                const regionText = region.options[region.selectedIndex].text;
                const provinceText = municipality.options[municipality.selectedIndex].text;
                const municipalityText = barangay.options[barangay.selectedIndex].text;

                let barangayText = '';
                if (barangaySelect && barangaySelect.selectedIndex > 0) {
                    barangayText = barangaySelect.options[barangaySelect.selectedIndex].text;
                }

                const streetText = street.value.trim();

                let fullAddress = `${streetText}`;
                if (barangayText) fullAddress += `, ${barangayText}`;
                fullAddress += `, ${municipalityText}, ${provinceText}, ${regionText}`;

                formData.append('address', fullAddress);

                // Show confirmation with SweetAlert2
                Swal.fire({
                    title: 'Confirm Registration',
                    html: `
                        <div class="text-left">
                            <p><strong>Name:</strong> ${formData.get('firstname')} ${formData.get('lastname')}</p>
                            <p><strong>Username:</strong> ${formData.get('username')}</p>
                            <p><strong>Email:</strong> ${formData.get('email')}</p>
                            <p><strong>Phone:</strong> ${formData.get('phone')}</p>
                            <p><strong>Address:</strong> ${fullAddress}</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, register me!',
                    cancelButtonText: 'Edit information',
                    customClass: {
                        confirmButton: 'rounded-xl px-4 py-2',
                        cancelButton: 'rounded-xl px-4 py-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Creating Account',
                            html: 'Please wait while we set up your account...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit the form using fetch API
                        fetch(form.action, {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Registration Successful!',
                                        html: `
                                        <div class="text-center">
                                            <svg class="mx-auto mb-4" width="64" height="64" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" fill="#22c55e" fill-opacity="0.2"/>
                                                <path d="M9 12l2 2 4-4" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                            </svg>
                                            <p>Your account has been created successfully.</p>
                                            <p class="text-sm text-slate-500 mt-2">You can now sign in with your credentials.</p>
                                        </div>
                                    `,
                                        icon: 'success',
                                        showConfirmButton: true,
                                        confirmButtonText: 'Go to Login',
                                        confirmButtonColor: '#3085d6',
                                        customClass: {
                                            confirmButton: 'rounded-xl px-4 py-2'
                                        }
                                    }).then(() => {
                                        window.location.href = 'login.php';
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Registration Failed',
                                        text: data.message || 'Something went wrong. Please try again.',
                                        icon: 'error',
                                        confirmButtonColor: '#3085d6',
                                        customClass: {
                                            confirmButton: 'rounded-xl px-4 py-2'
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'A network error occurred. Please try again.',
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6',
                                    customClass: {
                                        confirmButton: 'rounded-xl px-4 py-2'
                                    }
                                });
                            });
                    }
                });

                return false;
            });
        });
    </script>
</body>

</html>