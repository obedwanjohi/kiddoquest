<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Setup — Create Super Admin — KiddoQuest CBC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4">

    <div class="bg-slate-900 border-2 border-indigo-500/40 rounded-3xl shadow-2xl p-8 max-w-md w-full text-white relative">
        
        {{-- Banner Badge --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-3xl mx-auto mb-3">
                👑
            </div>
            <h1 class="text-2xl font-black text-white">Super Admin Setup</h1>
            <p class="text-xs text-indigo-300 font-semibold mt-1">Create the master administrator account for KiddoQuest</p>
            <div class="mt-2 inline-block bg-amber-500/20 border border-amber-500/40 text-amber-300 text-[11px] font-bold px-3 py-1 rounded-full">
                🔒 One-time registration • Auto-locks after setup
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-rose-500/20 border border-rose-500 text-rose-300 rounded-xl p-3 text-xs font-bold">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.setup.post') }}" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Full Admin Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Master Admin"
                       class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 font-bold text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Admin Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@kiddoquest.co.ke"
                       class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 font-bold text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="At least 8 characters"
                       class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 font-bold text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required placeholder="Re-type password"
                       class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 font-bold text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black py-4 rounded-xl text-sm shadow-xl transition-all cursor-pointer mt-2">
                Create Master Account & Lock Setup 🚀
            </button>
        </form>

        <div class="text-center mt-6 pt-4 border-t border-slate-800 text-xs">
            <a href="{{ route('admin.login') }}" class="text-indigo-400 hover:text-white font-bold">
                Already have an admin account? Sign In →
            </a>
        </div>

    </div>

</body>
</html>
