<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Jeepney Tracking — Sign in</title>
  <link rel="stylesheet" href="src/output.css" />
</head>

<body
  class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 bg-[radial-gradient(30%_40%_at_80%_0%,rgba(56,189,248,0.12),transparent_60%),radial-gradient(25%_35%_at_10%_10%,rgba(34,197,94,0.08),transparent_60%)]">
  <main class="min-h-svh grid place-items-center p-6">
    <section
      class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/70 p-6 shadow-xl backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/60 md:p-8"
      role="region" aria-labelledby="signin-title">
      <div class="flex items-center justify-center gap-3 mb-4 select-none">
        <div
          class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 bg-gradient-to-b from-sky-200/40 to-transparent text-[11px] font-bold tracking-wide dark:border-slate-800 dark:from-sky-400/20">
          JT</div>
        <div class="text-sm font-bold tracking-wide">Jeepney GPS Tracking</div>
      </div>

      <header class="mb-5">
        <h1 id="signin-title" class="flex items-center gap-2 text-xl font-bold">
          <span class="inline-block h-2 w-2 rounded-full bg-sky-500 shadow-[0_0_0_6px_rgba(14,165,233,0.12)]"></span>
          Sign in
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Welcome back. Please enter your details to continue.
        </p>
      </header>

      <?php if (isset($_SESSION['login_error'])): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
          <?php echo $_SESSION['login_error'];
          unset($_SESSION['login_error']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['register_success'])): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
          <?php echo $_SESSION['register_success'];
          unset($_SESSION['register_success']); ?>
        </div>
      <?php endif; ?>

      <form id="login-form" action="backend/login_process.php" method="POST" class="grid gap-4" novalidate>
        <div id="f-identity" class="grid gap-2">
          <label for="identity" class="text-[13px] font-semibold">Username</label>
          <div class="relative">
            <input id="identity" name="identity" type="text" autocomplete="username" placeholder="jeepfan_01" required
              class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
          </div>
          <p id="identity-error" class="hidden text-xs text-red-500">Please enter your username.</p>
        </div>

        <div id="f-password" class="grid gap-2">
          <label for="password" class="text-[13px] font-semibold">Password</label>
          <div class="relative">
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••"
              minlength="6" required
              class="w-full rounded-xl border border-slate-200 bg-white/60 px-4 py-3 pr-12 text-[15px] text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-200/60 dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-400/20" />
            <button id="toggle-pass" type="button" aria-label="Show password" aria-pressed="false"
              class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white/70 text-slate-500 transition hover:text-slate-900 dark:border-slate-800 dark:bg-slate-800/60 dark:hover:text-slate-100">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="opacity-80">
                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.6" />
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" />
              </svg>
            </button>
          </div>
          <p id="password-error" class="hidden text-xs text-red-500">Password must be at least 6 characters.</p>
        </div>

        <div class="mt-1 flex items-center justify-between">
          <a id="forgot-link" href="#"
            class="text-[13px] text-slate-500 underline-offset-4 hover:underline dark:text-slate-400">Forgot
            password?</a>
        </div>

        <div class="grid gap-3">
          <button type="submit"
            class="btn-primary inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-[0_8px_20px_rgba(2,6,23,0.12)] transition hover:shadow-[0_10px_26px_rgba(2,6,23,0.18)] active:translate-y-px dark:bg-white dark:text-slate-950">Continue</button>
          <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
            <span class="h-px bg-slate-200 dark:bg-slate-800"></span>
            <span>or</span>
            <span class="h-px bg-slate-200 dark:bg-slate-800"></span>
          </div>
          <a href="register.php"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/60 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-white dark:border-slate-800 dark:bg-transparent dark:text-slate-100 dark:hover:bg-slate-900/40">Create
            an account</a>
        </div>
      </form>

      <p class="mt-5 text-center text-[13px] text-slate-500 dark:text-slate-400">By continuing, you agree to our Terms
        and acknowledge our Privacy Policy.</p>
    </section>
  </main>

  <script>
    const form = document.getElementById('login-form');
    const identity = document.getElementById('identity');
    const password = document.getElementById('password');
    const idErr = document.getElementById('identity-error');
    const pwErr = document.getElementById('password-error');
    const toggle = document.getElementById('toggle-pass');

    function showError(el, msgEl, show) {
      el.classList.toggle('ring-4', show);
      el.classList.toggle('ring-red-300/40', show);
      el.classList.toggle('border-red-400', show);
      msgEl.classList.toggle('hidden', !show);
      el.setAttribute('aria-invalid', show ? 'true' : 'false');
    }

    function validate() {
      const idVal = identity.value.trim();
      const pwVal = password.value;
      const idInvalid = idVal.length === 0;
      const pwInvalid = pwVal.length < 6;
      showError(identity, idErr, idInvalid);
      showError(password, pwErr, pwInvalid);
      return !(idInvalid || pwInvalid);
    }

    toggle.addEventListener('click', () => {
      const isPass = password.type === 'password';
      password.type = isPass ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', isPass ? 'true' : 'false');
      toggle.setAttribute('aria-label', isPass ? 'Hide password' : 'Show password');
      password.focus({ preventScroll: true });
    });

    identity.addEventListener('input', () => showError(identity, idErr, false));
    password.addEventListener('input', () => showError(password, pwErr, false));

    form.addEventListener('submit', function (e) {
      if (!validate()) {
        e.preventDefault();
        return false;
      }
      return true;
    });

    document.getElementById('forgot-link').addEventListener('click', (e) => {
      e.preventDefault();
      alert('Password recovery functionality not implemented yet');
    });
  </script>
</body>

</html>