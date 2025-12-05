<footer class="bg-gray-800 text-white mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

        <!-- Hlavní obsah footeru -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">

            <!-- Sekce 1: O projektu -->
            <div>
                <h3 class="text-lg font-bold mb-4">O projektu</h3>
                <p class="text-gray-300 text-sm leading-relaxed">
                    Charitativní výzva 100 půlmaratonů za 100 dní na podporu dětského hospice Dům pro Julii.
                </p>
                <!-- Logo Dům pro Julii -->
                <div class="mt-4">
                    <a href="https://www.dumprojulii.com/" target="_blank" rel="noopener noreferrer" class="inline-block transition-opacity hover:opacity-80">
                        <img src="{{ asset('images/dum-pro-julii-logo-white.png') }}" alt="Dům pro Julii" class="h-20 w-auto">
                    </a>
                </div>
            </div>

            <!-- Sekce 2: Důležité odkazy -->
            <div>
                <h3 class="text-lg font-bold mb-4">Důležité odkazy</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('privacy_policy') }}" class="text-gray-300 hover:text-white transition-colors">
                            Zásady ochrany osobních údajů (GDPR)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user_data_deletion') }}" class="text-gray-300 hover:text-white transition-colors">
                            Žádost o smazání údajů
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('story') }}" class="text-gray-300 hover:text-white transition-colors">
                            Příběh projektu
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('results-jitka.index') }}" class="text-gray-300 hover:text-white transition-colors">
                            Výsledky
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sekce 3: Kontakt a partneři -->
            <div>
                <h3 class="text-lg font-bold mb-4">Kontakt</h3>
                <div class="text-sm text-gray-300 space-y-2 mb-4">
                    <p>
                        <strong>Provozovatel:</strong><br>
                        Martin Kupec<br>
                        
                    </p>
                    <p>
                        <strong>Email:</strong><br>
                        <a href="mailto:info@timechip.cz" class="hover:text-white transition-colors">
                            info@timechip.cz
                        </a>
                    </p>
                </div>

                <!-- Partneři -->
                <div>
                    <h4 class="text-sm font-semibold mb-2">Technologičtí partneři</h4>
                    <div class="flex items-center gap-4">
                        <!-- Strava Logo -->
                        <a href="https://www.strava.com" target="_blank" rel="noopener noreferrer" class="transition-opacity hover:opacity-80">
                            <img src="{{ asset('images/strava-logo.png') }}" alt="Strava" class="h-8 w-auto">
                        </a>
                        <!-- Stripe Logo -->
                        <a href="https://stripe.com" target="_blank" rel="noopener noreferrer" class="transition-opacity hover:opacity-80">
                            <svg class="h-8 w-auto" viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.10 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.9 0 1.85 6.29.97 6.29 5.88z" fill="#fff"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spodní lišta -->
        <div class="border-t border-gray-700 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                <p class="mb-2 md:mb-0">
                    &copy; {{ date('Y') }} liferun.cz - Všechna práva vyhrazena
                </p>
                <p class="text-xs">
                    Provozováno s láskou pro dobrou věc
                </p>
            </div>
        </div>

    </div>
</footer>
