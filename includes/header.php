<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?><?php echo defined('SITE_NAME') ? SITE_NAME : 'CampusWear'; ?></title>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : ''; ?>&currency=<?php echo defined('PAYPAL_CURRENCY') ? PAYPAL_CURRENCY : 'PHP'; ?>"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
