<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>{{ $title ?? 'Sign In | DevFolio.AI' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #030712;
            color: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen antialiased bg-gray-950 text-gray-100 flex flex-col justify-between overflow-x-hidden w-full font-body">
    {{ $slot }}
    @livewireScripts

    <script>
        function autofillForm(email, password, emailId, passwordId) {
            const emailInput = document.getElementById(emailId) || document.querySelector('input[type="email"]');
            const passInput = document.getElementById(passwordId) || document.querySelector('input[type="password"]');
            
            if (emailInput) {
                emailInput.value = email;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = password;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    </script>
</body>
</html>
