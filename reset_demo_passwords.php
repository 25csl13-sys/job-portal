<?php
// Use ONLY if demo logins fail. Open once in the browser, then delete this file.
require_once 'config.php';
$hash = password_hash('password', PASSWORD_DEFAULT);
$st = $conn->prepare("UPDATE users SET password=? WHERE email IN ('admin@jobportal.com','hr@technova.com','hr@greenleaf.com','hr@pixelworks.com','arjun@mail.com','divya@mail.com','karthik@mail.com')");
$st->bind_param('s', $hash);
$st->execute();
echo 'Demo passwords reset to "password". Delete this file now.';
