<x-full-width-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">

                    <div class="text-center">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-gray-100">
                            About Us: The TLDR AI Story
                        </h1>
                        <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">
                            At TLDR AI, our mission is simple: to make knowledge accessible and effortless. We believe that in a world of information overload, clarity is the most valuable asset. We built TLDR AI to be your intelligent partner, helping you cut through the noise and get straight to the point.
                        </p>
                    </div>

                    <hr class="my-6 border-gray-300 dark:border-gray-700">

                    <div class="max-w-4xl mx-auto">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">The Challenge of Information Overload</h2>
                        <p class="text-gray-700 dark:text-gray-300">
                            We've all been there—faced with a dense report, a lengthy research paper, or a mountain of documents and not enough time to read them all. The 'Too Long; Didn't Read' (TL;DR) shorthand isn't a sign of laziness; it's a cry for efficiency. Manually summarizing text is slow, prone to error, and often misses crucial details. This bottleneck prevents us from being as informed and productive as we could be.
                        </p>
                    </div>

                    <div class="max-w-4xl mx-auto">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Our Intelligent Solution</h2>
                        <p class="text-gray-700 dark:text-gray-300">
                            TLDR AI solves this problem with cutting-edge <strong>artificial intelligence</strong> and <strong>natural language processing (NLP)</strong>. Our advanced algorithms don't just shorten text; they intelligently analyze it to identify core themes, extract essential facts, and condense complex ideas into concise, accurate summaries. We provide you with the essence of the document, so you can make faster, smarter decisions.
                        </p>
                    </div>

                    <div class="max-w-4xl mx-auto">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Why Choose TLDR AI?</h2>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                            <li><strong>Pinpoint Accuracy</strong>: Our AI is trained to distinguish critical information from filler content, ensuring every summary is valuable and relevant.</li>
                            <li><strong>Blazing Speed</strong>: Get comprehensive summaries in seconds, freeing up hours of your time for higher-level tasks.</li>
                            <li><strong>Intuitive Design</strong>: Our user-friendly interface makes it easy to upload any document and get a summary without a learning curve.</li>
                            <li><strong>Unmatched Versatility</strong>: Whether it's an academic paper, a business report, a legal brief, or a news article, TLDR AI handles it all with precision.</li>
                        </ul>
                        <p class="mt-4 text-center text-lg font-semibold text-gray-800 dark:text-gray-200">
                            We're not just creating summaries; we're providing an intelligent shortcut to knowledge. Join us and transform the way you read, learn, and work.
                        </p>
                    </div>
                </div>

                <hr class="my-6 border-gray-300 dark:border-gray-700"> {{-- Added this hr for visual separation of CTAs --}}

                <div class="p-6 text-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Get Started for Free
                        </a>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Already have an account?
                            <a href="{{ route('login') }}" class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-800">
                                Log in
                            </a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-full-width-layout>