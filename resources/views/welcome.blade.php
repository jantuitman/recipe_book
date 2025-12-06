<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AI Recipe Book - Your Personal Recipe Assistant</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-[#F4F1DE]">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-2xl font-serif font-bold text-[#E07A5F]">
                            AI Recipe Book
                        </a>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-[#3D405B] hover:text-[#E07A5F] transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-[#3D405B] hover:text-[#E07A5F] transition">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="bg-[#E07A5F] text-white px-6 py-2 rounded-lg hover:bg-[#d06b50] transition">
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-serif font-bold text-[#3D405B] mb-6">
                    Your Personal Recipe Book,<br>Powered by AI
                </h1>
                <p class="text-xl text-[#1F2937] max-w-3xl mx-auto mb-10 leading-relaxed">
                    Store, organize, and improve your recipes with the help of artificial intelligence.
                    Paste unstructured recipe text and let AI extract ingredients and steps.
                    Get personalized suggestions to make your recipes even better.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="bg-[#E07A5F] text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-[#d06b50] transition shadow-lg">
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="bg-white text-[#3D405B] px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition border-2 border-[#3D405B]">
                        Login
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-4xl font-serif font-bold text-center text-[#3D405B] mb-12">
                    Features
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1: AI Parsing -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#E07A5F] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            AI-Powered Recipe Parsing
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Paste any recipe text and let AI automatically extract ingredients, quantities, and step-by-step instructions. No more manual formatting!
                        </p>
                    </div>

                    <!-- Feature 2: Chat Assistant -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#81B29A] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            AI Chat Assistant
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Brainstorm new recipes, ask cooking questions, and get suggestions for ingredient substitutions with your personal AI cooking assistant.
                        </p>
                    </div>

                    <!-- Feature 3: Version History -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#3D405B] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            Recipe Versioning
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Keep track of recipe improvements over time. Get AI feedback on your recipes and create new versions with suggested changes.
                        </p>
                    </div>

                    <!-- Feature 4: Unit Conversion -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#E07A5F] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            Smart Unit Conversion
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Automatically convert between metric and imperial units. Display ingredients in your preferred measurement system.
                        </p>
                    </div>

                    <!-- Feature 5: Serving Size Scaling -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#81B29A] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            Serving Size Modifier
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Easily scale ingredient quantities up or down based on the number of servings you need. Perfect for cooking for crowds or solo meals.
                        </p>
                    </div>

                    <!-- Feature 6: Personal Collection -->
                    <div class="bg-[#F4F1DE] rounded-lg p-8 shadow-md hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-[#3D405B] rounded-full flex items-center justify-center mb-6 mx-auto">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-3 text-center">
                            Your Personal Collection
                        </h3>
                        <p class="text-[#1F2937] text-center">
                            Store all your recipes in one secure place. Search by name or ingredient, and organize your culinary creations your way.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-4xl font-serif font-bold text-center text-[#3D405B] mb-12">
                    How It Works
                </h2>
                <div class="space-y-8">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-[#E07A5F] rounded-full flex items-center justify-center text-white text-xl font-bold">
                            1
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-2">
                                Sign Up Free
                            </h3>
                            <p class="text-[#1F2937]">
                                Create your account in seconds. No credit card required, no email verification needed.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-[#E07A5F] rounded-full flex items-center justify-center text-white text-xl font-bold">
                            2
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-2">
                                Add Your Recipes
                            </h3>
                            <p class="text-[#1F2937]">
                                Paste recipe text from anywhere—websites, cookbooks, or handwritten notes. Our AI will structure it automatically.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-[#E07A5F] rounded-full flex items-center justify-center text-white text-xl font-bold">
                            3
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-serif font-bold text-[#3D405B] mb-2">
                                Cook and Improve
                            </h3>
                            <p class="text-[#1F2937]">
                                Use your recipes, provide feedback, and let AI suggest improvements. Build your perfect recipe collection over time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#3D405B]">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-serif font-bold text-white mb-6">
                    Ready to Transform Your Cooking?
                </h2>
                <p class="text-xl text-gray-200 mb-8">
                    Join AI Recipe Book today and start building your perfect recipe collection.
                </p>
                <a href="{{ route('register') }}" class="inline-block bg-[#E07A5F] text-white px-10 py-4 rounded-lg text-lg font-semibold hover:bg-[#d06b50] transition shadow-lg">
                    Get Started Free
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center text-[#1F2937]">
                <p>&copy; {{ date('Y') }} AI Recipe Book. Powered by Laravel and OpenAI.</p>
            </div>
        </footer>
    </body>
</html>
