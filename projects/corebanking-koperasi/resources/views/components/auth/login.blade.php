<main class="min-h-screen flex items-center justify-center p-4 md:p-0">
    <div class="w-full max-w-5xl grid md:grid-cols-2 overflow-hidden rounded-xl shadow-2xl shadow-primary/5">

        <!-- Left Side: Gradient Banner -->
        <div class="hidden md:flex pearl-gradient relative p-12 flex-col justify-between overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-tertiary/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary-container/30 rounded-full blur-2xl -ml-24 -mb-24">
            </div>
            <div class="relative z-10">
                <h1 class="font-headline font-extrabold text-4xl lg:text-5xl text-white tracking-tight leading-tight">
                    Core Banking <br /> <span class="text-tertiary-fixed">Koperasi</span>
                </h1>
                <p class="mt-6 text-white text-sm font-bold opacity-90 uppercase tracking-wider">Powered by PT Moduvox Tech ID</p>
            </div>
            <div class="relative z-10">
                <div class="h-1 w-12 bg-tertiary mb-6"></div>
                <p class="text-on-primary-container text-lg leading-relaxed max-w-sm">
                    Akses layanan keuangan eksklusif dan kelola aset Anda dengan keamanan standar institusi.
                </p>
            </div>
            <div class="absolute inset-0 opacity-20"
                style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDBgX8Cs4gACrDwbNtleU184mBEAO2DCBnBnmuGvYLSHFrH1__-vxDa0gcjhgCIzg3oLGICaEW95usbaR0ADme7B7BMW2F0BoVni3Im-uw18mabFgf9zL_63_JaNETMXjBWtmfFqSyvF5ere6QIwKsQc51jZ1QPlr68KWEly2A8N_rljoc0hwBo_TeJxmsBx2T2VhWM_zPPt7BfkJWxJ6BaJ-KekpaNTxz4scFR5NZQ8pA-6k4t9ry4Pp_DzsSSvzWthEXJ3AAtd24');">
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="bg-surface-container-lowest p-8 md:p-16 flex flex-col justify-center">
            <div class="mb-10 block md:hidden text-center">
                <p class="text-primary text-sm font-bold uppercase tracking-wider">PT Moduvox Tech ID</p>
            </div>

            <div class="mb-8">
                <h2 class="font-headline font-bold text-2xl text-primary mb-2">Akses Demo Aplikasi</h2>
                <p class="text-on-surface-variant text-sm">Masuk secara otomatis untuk mencoba fitur-fitur Core Banking.</p>
            </div>

            <!-- Login Error Feedback -->
            @error('identifier')
            <div class="mb-4 text-sm text-error font-medium">
                {{ $message }}
            </div>
            @enderror

            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Info Message -->
                <div class="bg-primary/10 border-l-4 border-primary p-4 rounded-r-lg">
                    <p class="text-sm text-primary font-medium">
                        Anda berada di mode Demo. Klik tombol di bawah ini untuk langsung login sebagai Administrator tanpa perlu memasukkan kredensial.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button
                        class="w-full bg-primary text-on-primary py-4 rounded-xl font-headline font-bold text-sm tracking-wide hover:bg-primary-container transition-all shadow-lg shadow-primary/10 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-2"
                        type="submit" wire:loading.attr="disabled">
                        <span class="material-symbols-outlined text-lg">login</span>
                        <span wire:loading.remove wire:target="login">Login Demo Otomatis</span>
                        <span wire:loading wire:target="login">Memproses...</span>
                    </button>
                </div>
            </form>

            <div class="mt-12 flex items-center justify-center space-x-4 opacity-50">
                <span class="material-symbols-outlined text-xs">verified_user</span>
                <span class="text-[10px] font-label uppercase tracking-[0.2em] text-on-surface-variant">Secure
                    Bank-Level Encryption</span>
            </div>
        </div>
    </div>
</main>