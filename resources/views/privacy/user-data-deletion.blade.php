@section('title', '| Žádost o smazání údajů')

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Hlavní content box -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-6 sm:p-8 md:p-12">

                <!-- Hlavička -->
                <div class="flex items-center justify-center gap-4 mb-8">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 text-center">
                        Žádost o smazání uživatelských dat
                    </h1>
                </div>

                <div class="prose prose-lg max-w-none text-gray-700">

                    <p class="text-lg leading-relaxed mb-8">
                        Pokud chcete smazat svůj účet a veškerá osobní data spojená s vaším profilem, postupujte následovně:
                    </p>

                    <!-- Postup -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Postup smazání dat</h2>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 sm:p-8 mb-8 border border-gray-200">
                        <ol class="list-decimal list-inside space-y-4 text-lg">
                            <li class="leading-relaxed">Přihlaste se do aplikace.</li>
                            <li class="leading-relaxed">Přejděte do nastavení účtu nebo profilu.</li>
                            <li class="leading-relaxed">Vyberte možnost "Smazat účet" nebo "Požádat o smazání dat".</li>
                            <li class="leading-relaxed">Potvrďte svou žádost.</li>
                        </ol>
                    </div>

                    <hr class="my-10 border-gray-300">

                    <!-- Alternativní způsob -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Alternativní způsob</h2>
                    </div>

                    <div class="bg-green-50 rounded-xl p-6 sm:p-8 border border-green-200 mb-8">
                        <p class="text-lg leading-relaxed mb-4">
                            Alternativně nám můžete zaslat žádost o smazání vašich dat na email:
                        </p>
                        <p class="text-center">
                            <a href="mailto:info@timechip.cz" class="inline-flex items-center gap-2 text-2xl font-bold text-white bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                info@timechip.cz
                            </a>
                        </p>
                    </div>

                    <hr class="my-10 border-gray-300">

                    <!-- Časový rámec -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Časový rámec zpracování</h2>
                    </div>

                    <div class="bg-orange-50 rounded-xl p-6 sm:p-8 border border-orange-200 mb-8">
                        <p class="text-lg leading-relaxed">
                            Po obdržení vaší žádosti smažeme všechna vaše osobní data z našich záznamů do <strong>30 pracovních dnů</strong>.
                        </p>
                    </div>

                    <hr class="my-10 border-gray-300">

                    <!-- Co bude smazáno -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Co bude smazáno</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-4">
                        Po zpracování vaší žádosti budou trvale odstraněny následující údaje:
                    </p>

                    <ul class="list-disc list-inside space-y-3 mb-8 text-lg">
                        <li>Vaše osobní identifikační údaje (jméno, email)</li>
                        <li>Váš profil a přihlašovací údaje</li>
                        <li>Historie plateb a transakcí</li>
                        <li>Všechna data spojená s vaším účtem</li>
                        <li>Záznamy o vaší aktivitě v aplikaci</li>
                    </ul>

                    <div class="bg-yellow-50 rounded-xl p-6 sm:p-8 border border-yellow-200 mb-8">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Upozornění</h3>
                                <p class="text-lg leading-relaxed text-gray-700">
                                    Některé údaje můžeme být povinni uchovat po zákonem stanovenou dobu z důvodu účetních nebo právních požadavků. Tyto údaje budou bezpečně uchovány a nebudou dále použity k žádným jiným účelům.
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-10 border-gray-300">

                    <!-- Kontakt -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Máte otázky?</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-8">
                        Pokud máte jakékoli dotazy ohledně smazání vašich dat nebo potřebujete další informace, neváhejte nás kontaktovat na <a href="mailto:info@timechip.cz" class="text-blue-600 hover:text-blue-800 underline font-semibold">info@timechip.cz</a>.
                    </p>

                    <!-- Tlačítka -->
                    <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="{{ route('privacy_policy') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg transition-colors text-xl">
                            Zásady ochrany osobních údajů
                        </a>
                        <a href="{{ route('index') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg transition-colors text-xl">
                            ← Zpět na hlavní stránku
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
