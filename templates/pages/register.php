<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-wrapper">
    <main class="auth-card">
        <header>
            <h2>User Registration</h2>
        </header>

        <form action="/register" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <footer>
            <p>
                <a href="/login">Already have an account? Back to login.</a>
            </p>
        </footer>
    </main>
</body>
</html>
