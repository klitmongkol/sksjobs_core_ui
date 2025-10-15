<template>
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
</template>