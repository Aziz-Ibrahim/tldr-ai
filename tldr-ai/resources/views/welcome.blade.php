<x-full-width-layout>
    <div class="relative min-h-screen flex flex-col items-center justify-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        
        <div class="mt-2 mb-6 text-center max-w-4xl px-4">
            <img src="{{ asset('images/tldr-ai-logo.svg') }}" alt="TLDR AI Logo" class="mx-auto mb-6 h-32">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight">
                <span class="text-indigo-500 dark:text-indigo-600">T</span>oo <span class="text-indigo-500 dark:text-indigo-600">L</span>ong; <span class="text-indigo-500 dark:text-indigo-600">D</span>idn't <span class="text-indigo-500 dark:text-indigo-600">R</span>ead? <br>Now you can, with <span class="text-indigo-500 dark:text-indigo-600">AI</span>.
            </h1>
        </div>

        <div class="text-center max-w-3xl px-4">
            <p class="text-xl sm:text-2xl text-gray-600 dark:text-gray-300 leading-relaxed mb-8">
                Drowning in documents, reports, and articles? We get it. Time is precious, and information overload is real.
            </p>
            <p class="text-xl sm:text-2xl text-gray-600 dark:text-gray-300 leading-relaxed mb-10">
                <strong>TLDR AI</strong> is your smart solution. Upload any long document and instantly receive a concise, AI-powered summary, highlighting the crucial points you need to know.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-12">
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Start Summarizing (Free!)
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Log In
                </a>
                <a href="{{ route('about') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                    Learn More
                </a>
            </div>
        </div>

    </div>
</x-full-width-layout>