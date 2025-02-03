<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h1>Hello, {{ $name }}</h1>
    <p>You requested to reset your password. Please click the link below to reset it:</p>
    <a href="{{ $resetUrl }}" style="padding: 10px; background: rgb(0, 140, 255); color: white; text-decoration: none; font-weight: 600;">Reset Password</a>
    <br>
    <br>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
