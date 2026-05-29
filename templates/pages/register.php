<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
        <h2>User Registration</h2>
        <form action="/register" method="POST">
            <div style="margin-bottom: 10px;">
                <label>Username:</label><br>
                <input type="text" name="username" required>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Email:</label><br>
                <input type="email" name="email" required>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Password:</label><br>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Create account</button>
        </form>
        <p><a href="/login">Back to login</a></p>
    </div>
</body>
</html>