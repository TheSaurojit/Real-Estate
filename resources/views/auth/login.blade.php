<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real Estate Management Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 font-sans text-slate-100 selection:bg-sky-500 selection:text-white">

    <div class="w-full max-w-md">
        
        <!-- Header / Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 shadow-xl shadow-sky-500/20 mb-4 ring-4 ring-sky-500/20">
                <i class="fa-solid fa-building text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Real Estate ERP Studio</h1>
            <p class="text-sm text-sky-400 mt-1">Property Booking & Accountability Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/90 backdrop-blur-md rounded-2xl border border-slate-700/80 shadow-2xl p-6 sm:p-8">
            
            <h2 class="text-lg font-semibold text-white mb-6 border-b border-slate-700/80 pb-3 flex items-center justify-between">
                <span>User Authentication</span>
                <span class="text-xs font-normal text-slate-400"><i class="fa-solid fa-lock mr-1"></i> Secure Login</span>
            </h2>

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/60 text-rose-300 text-xs">
                    <div class="font-semibold mb-1 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-rose-400"></i> Authentication Failed
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- User ID / Email -->
                <div>
                    <label for="login_id" class="block text-xs font-medium uppercase tracking-wider text-slate-300 mb-1.5">
                        User ID / Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" id="login_id" name="login_id" value="{{ old('login_id', 's4subhasish@gmail.com') }}" required autofocus
                               placeholder="e.g. SSI/USR-1001 or email@domain.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Enter your auto-assigned User ID or registered Email</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-medium uppercase tracking-wider text-slate-300 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <input type="password" id="password" name="password" value="password" required
                               placeholder="••••••••••••"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-sky-600 focus:ring-sky-500">
                        <span>Remember credentials</span>
                    </label>
                    <span class="text-xs text-slate-400">Default password: <code class="text-sky-300">password</code></span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-sky-500/25 focus:outline-none focus:ring-2 focus:ring-sky-400 transition transform active:scale-[0.99] flex items-center justify-center space-x-2 text-sm mt-4">
                    <span>Access ERP Workspace</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>

            </form>

        </div>

        <!-- Footer Info Box -->
        <div class="mt-6 text-center text-xs text-slate-500">
            <p>Real Estate Property & Financial Management Platform</p>
        </div>

    </div>

</body>
</html>
