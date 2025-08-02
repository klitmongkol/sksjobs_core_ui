<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sks Login With LINE</title>
        {{-- LIFF SDK Loader - ต้องอยู่ตรงนี้ และ 'ก่อน' @vite(['resources/js/app.js']) --}}
        <script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Bootstrap CDN --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <div id="app" class="container mt-5">
            <h1 class="text-center mb-4">SKS Login With LINE v1.4.3</h1>
            <line-login-button></line-login-button>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    </body>
</html>