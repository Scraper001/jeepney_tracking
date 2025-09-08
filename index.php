<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeepney Tracking System</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to CSS for styling -->
</head>
<body>
    <!-- Hero Section -->
    <header>
        <div class="hero">
            <h1>Welcome to the Jeepney Tracking System</h1>
            <p>Track your jeepneys in real-time!</p>
            <a href="#signup" class="cta-button">Get Started</a>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features">
        <h2>Features</h2>
        <ul>
            <li>Real-time tracking of jeepneys</li>
            <li>User-friendly interface</li>
            <li>Notifications for jeepney arrival</li>
            <li>Route optimization</li>
        </ul>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works">
        <h2>How It Works</h2>
        <p>Our system uses GPS tracking to provide real-time data on jeepney locations. Users can view the nearest jeepneys and receive notifications when they are close by.</p>
    </section>

    <!-- Login/Signup Section -->
    <section id="login-signup">
        <h2>Login / Signup</h2>
        <form id="signup" action="signup.php" method="post">
            <h3>Signup</h3>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <form action="login.php" method="post">
            <h3>Login</h3>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </section>
</body>
</html>