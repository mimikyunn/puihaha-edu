<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<main class="grid md:grid-cols-[65%_35%] bg-linear-[#270CC5_0%,#1800A6_50%,#13065F_100%] min-h-screen">
    <!-- CTA SECTION -->
    <section class="hidden lg:flex flex-col justify-end p-14 text-white">
        <h1 class="text-5xl font-bold">Lorem Ipsum</h1>

        <p class="mt-4 text-md">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.
        </p>
    </section>

    <!-- LOGIN SECTION -->
    <section class="flex items-center justify-center px-6 py-10 sm:px-10 lg:px-8 min-h-dvh bg-white">
        <div class="w-full max-w-md">

            <!-- PUIHAHA LOGO -->
            <span class="flex mb-8 items-center justify-center gap-3 text-3xl font-bold text-[#270CC5]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-yellow-400 size-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                </svg>
                PUIHAHA
            </span>

            <!-- LOGIN FORM -->
            <h2 class="text-2xl font-bold text-slate-800">Welcome Back!</h2>
            <p class="mt-2 text-md text-slate-600">Please login to your account.</p>

            <form class="mt-7">
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-md font-medium text-slate-800">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email"
                        class="w-full px-4 py-2 border-2 border-neutral-200 bg-neutral-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#270CC5]">
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block mb-2 text-md font-medium text-slate-800">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password"
                        class="w-full px-4 py-2 border-2 border-neutral-200 bg-neutral-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#270CC5]">
                </div>

                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 text-sm">
                    <!-- Remember Me Checkbox -->
                    <label class="flex items-center gap-2 text-slate-800 cursor-pointer">
                        <input type="checkbox" name="remember" class="size-3.5 accent-[#270CC5]" />
                        Remember me
                    </label>
                    <!-- Forgot Password Link -->
                    <a href="#" class="text-sm text-[#270CC5] hover:underline">
                        Forgot Password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full px-4 py-2 mt-4 text-white bg-[#270CC5] rounded-lg hover:bg-[#1800A6] cursor-pointer">
                    Login
                </button>

                <!-- Signup Link -->
                <p class="mt-10 text-sm text-center text-slate-600">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-[#270CC5] hover:underline">Sign up!</a>
                </p>
            </form>
        </div>
    </section>

</main>