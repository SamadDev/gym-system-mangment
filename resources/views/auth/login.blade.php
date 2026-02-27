<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Kurdistan Gym Management</title>
    @vite(['resources/js/src/main.ts'])
</head>
<body>
    <div id="app">
        <login-page></login-page>
    </div>
</body>
</html>
