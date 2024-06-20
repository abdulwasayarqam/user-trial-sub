<!DOCTYPE html>
<html>
<head>
    <title>Subscription Confirmation</title>
</head>
<body>
    <h1>Thank You for Subscribing!</h1>
    <p>Dear {{ $user->name }},</p>
    <p>Thank you for subscribing to our {{ $planName }} plan. A confirmation PDF is attached with this email.</p>
    <p>Best regards,<br>Our Company</p>
</body>
</html>
