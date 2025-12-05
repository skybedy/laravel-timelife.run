@section('title', '| Zásady ochrany osobních údajů')

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Hlavní content box -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-6 sm:p-8 md:p-12">

                <!-- Hlavička -->
                <div class="flex items-center justify-center gap-4 mb-8">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 text-center">
                        Zásady ochrany osobních údajů
                    </h1>
                </div>

                <div class="prose prose-lg max-w-none text-gray-700">

                    <p class="text-center text-gray-600 mb-8">Poslední aktualizace: {{ date('d.m.Y') }}</p>

                    <p class="text-lg leading-relaxed mb-6">
                        <strong>liferun.cz</strong> ("my", "nás", nebo "naše") provozuje webovou stránku liferun.cz (dále jen "Služba").
                    </p>
                    <p class="text-lg leading-relaxed mb-8">
                        Tato stránka vás informuje o našich zásadách týkajících se shromažďování, používání a zveřejňování osobních údajů, když používáte naši Službu, a o volbách, které máte s těmito údaji spojené.
                    </p>
                    <p class="text-lg leading-relaxed mb-8">
                        Vaše data používáme k poskytování a zlepšování Služby. Používáním Služby souhlasíte se shromažďováním a používáním informací v souladu s těmito zásadami.
                    </p>

                    <hr class="my-10 border-gray-300">

                    <!-- Shromažďované údaje -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Shromažďované údaje</h2>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Osobní údaje</h3>
                    <p class="text-lg leading-relaxed mb-4">
                        Když používáte naši Službu, můžeme vás požádat o poskytnutí určitých osobně identifikovatelných informací, které mohou zahrnovat, mimo jiné:
                    </p>
                    <ul class="list-disc list-inside space-y-2 mb-8 text-lg">
                        <li>Jméno</li>
                        <li>Emailová adresa</li>
                        <li>Profil na sociálních sítích (například přihlašování přes Facebook či Strava)</li>
                    </ul>

                    <h3 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Údaje o používání</h3>
                    <p class="text-lg leading-relaxed mb-4">
                        Můžeme také shromažďovat informace o tom, jak je Služba přistupována a používána, jako například:
                    </p>
                    <ul class="list-disc list-inside space-y-2 mb-8 text-lg">
                        <li>IP adresa</li>
                        <li>Typ prohlížeče a jeho verze</li>
                        <li>Stránky naší Služby, které navštěvujete</li>
                        <li>Čas a datum vaší návštěvy</li>
                        <li>Další diagnostické údaje</li>
                    </ul>

                    <hr class="my-10 border-gray-300">

                    <!-- Jak údaje používáme -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Jak vaše údaje používáme</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-4">Shromážděné údaje používáme k různým účelům:</p>
                    <ul class="list-disc list-inside space-y-2 mb-8 text-lg">
                        <li>Provoz a údržba Služby</li>
                        <li>Upozornění na změny v naší Službě</li>
                        <li>Možnost zúčastnit se interaktivních funkcí naší Služby</li>
                        <li>Poskytování zákaznické podpory</li>
                        <li>Analýza a zlepšení Služby</li>
                        <li>Monitorování používání Služby</li>
                    </ul>

                    <hr class="my-10 border-gray-300">

                    <!-- Zveřejňování údajů -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Zveřejňování údajů</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-4">
                        Vaše osobní údaje neprodáváme, nevyměňujeme ani nepronajímáme třetím stranám. Vaše údaje můžeme zveřejnit pouze v následujících případech:
                    </p>
                    <ul class="list-disc list-inside space-y-2 mb-8 text-lg">
                        <li>Pokud to vyžaduje zákon</li>
                        <li>Na ochranu a obranu našich práv nebo majetku</li>
                        <li>Pro prevenci nebo vyšetřování možného protiprávního jednání v souvislosti se Službou</li>
                    </ul>

                    <hr class="my-10 border-gray-300">

                    <!-- Bezpečnost údajů -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Bezpečnost údajů</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-8">
                        Vaše osobní údaje považujeme za důvěrné a zavazujeme se je chránit. Nicméně, žádný způsob přenosu přes internet ani způsob elektronického ukládání není 100% bezpečný. Snažíme se používat komerčně přiměřené prostředky k ochraně vašich osobních údajů, ale nemůžeme zaručit jejich absolutní bezpečnost.
                    </p>

                    <hr class="my-10 border-gray-300">

                    <!-- Vaše práva -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Vaše práva týkající se osobních údajů</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-4">
                        V závislosti na vaší lokalitě máte určité práva týkající se vašich osobních údajů, jako například:
                    </p>
                    <ul class="list-disc list-inside space-y-2 mb-6 text-lg">
                        <li>Právo na přístup k vašim osobním údajům</li>
                        <li>Právo na opravu nebo smazání vašich údajů</li>
                        <li>Právo vznést námitku proti zpracování údajů</li>
                        <li>Právo požádat o přenos vašich údajů k jinému poskytovateli</li>
                    </ul>
                    <p class="text-lg leading-relaxed mb-8">
                        Chcete-li uplatnit tato práva, kontaktujte nás na <a href="mailto:info@timechip.cz" class="text-blue-600 hover:text-blue-800 underline font-semibold">info@timechip.cz</a>.
                    </p>

                    <hr class="my-10 border-gray-300">

                    <!-- Uchovávání údajů -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Uchovávání údajů</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-8">
                        Vaše osobní údaje uchováváme po dobu nezbytnou k poskytování naší Služby nebo k plnění našich právních povinností. Poté jsou vaše údaje bezpečně vymazány.
                    </p>

                    <hr class="my-10 border-gray-300">

                    <!-- Změny těchto zásad -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-pink-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Změny těchto zásad</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-8">
                        Tyto Zásady ochrany osobních údajů můžeme čas od času aktualizovat. O změnách vás budeme informovat tím, že zveřejníme nové Zásady na této stránce. Doporučujeme vám pravidelně kontrolovat tyto zásady.
                    </p>

                    <hr class="my-10 border-gray-300">

                    <!-- Kontakt -->
                    <div class="flex items-center gap-3 mt-10 mb-6">
                        <svg class="w-8 h-8 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 m-0">Kontakt</h2>
                    </div>

                    <p class="text-lg leading-relaxed mb-8">
                        Máte-li jakékoli dotazy ohledně těchto Zásad ochrany osobních údajů, kontaktujte nás na <a href="mailto:info@timechip.cz" class="text-blue-600 hover:text-blue-800 underline font-semibold">info@timechip.cz</a>.
                    </p>

                    <!-- Zpět na hlavní stránku -->
                    <div class="mt-12 text-center">
                        <a href="{{ route('index') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg transition-colors text-xl">
                            ← Zpět na hlavní stránku
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
