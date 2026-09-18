<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0284c7">
    <meta name="description" content="FieldOps KSA - منصة إدارة العمليات الميدانية للمقاولات">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FieldOps KSA">
    <link rel="manifest" href="/manifest.json">
    <title>FieldOps KSA | إدارة العمليات الميدانية للمقاولات</title>

    <!-- Google Fonts: Inter & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <!-- Inline anti-FOUC script for locale and direction -->
    <script>
        (function() {
            var saved = localStorage.getItem('fieldops_locale');
            if (saved === 'en') {
                document.documentElement.setAttribute('lang', 'en');
                document.documentElement.setAttribute('dir', 'ltr');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen selection:bg-amber-500 selection:text-white">
    <div id="app"></div>
</body>
</html>
