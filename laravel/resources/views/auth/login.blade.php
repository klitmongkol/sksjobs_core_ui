<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <script src="https://static.line-scdn.net/liff/edge/versions/2.22.3/sdk.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="text-center mb-3">SKS LOGIN</div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="col-6 mb-3 mx-auto">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-6 mb-3 mx-auto">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-6 mb-3 mx-auto text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
        <script>localStorage.clear();</script>
    </body>
</html>