<script setup lang="ts">
/*
 * Trust signali za srpsko tržište — pouzeće je primarni jer je 60-70% kupaca COD.
 * Logoi kurira/payment providera su placeholder text-card-ovi (uniformni stil) — zameni
 * pravim SVG-ovima kad se pribave od kurirskih i payment partner-a.
 * Link "Povraćaj 14 dana" vodi na CMS stranicu sa zakonskim tekstom (čl. 28 ZZP).
 */
</script>

<template>
  <section class="border-y border-gray-200 bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Glavni trust signali — 2x2 mobile, 4x1 desktop -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- 1. Pouzeće (primarni — accent boja jer je 60%+ kupaca COD) -->
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-accent-500 flex items-center justify-center flex-shrink-0">
            <Icon name="lucide:banknote" class="w-5 h-5 text-gray-900" />
          </div>
          <div>
            <p class="text-[13px] font-bold text-gray-900">Plaćanje pouzećem</p>
            <p class="text-[12px] text-gray-600 leading-snug mt-0.5">Gotovinom kuriru na adresi</p>
          </div>
        </div>

        <!-- 2. Brza dostava -->
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-primary-100 flex items-center justify-center flex-shrink-0">
            <Icon name="lucide:truck" class="w-5 h-5 text-primary-700" />
          </div>
          <div>
            <p class="text-[13px] font-bold text-gray-900">1–2 radna dana</p>
            <p class="text-[12px] text-gray-600 leading-snug mt-0.5">Post Express, BEX, AKS, D-Express</p>
          </div>
        </div>

        <!-- 3. Povraćaj 14 dana — link na ZZP -->
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-primary-100 flex items-center justify-center flex-shrink-0">
            <Icon name="lucide:rotate-ccw" class="w-5 h-5 text-primary-700" />
          </div>
          <div>
            <p class="text-[13px] font-bold text-gray-900">Povraćaj 14 dana</p>
            <p class="text-[12px] text-gray-600 leading-snug mt-0.5">
              Po Zakonu o zaštiti potrošača ·
              <NuxtLink to="/uslovi-koriscenja#reklamacije" class="text-primary-700 underline-offset-2 hover:underline">detalji</NuxtLink>
            </p>
          </div>
        </div>

        <!-- 4. Sigurno plaćanje -->
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 bg-primary-100 flex items-center justify-center flex-shrink-0">
            <Icon name="lucide:shield-check" class="w-5 h-5 text-primary-700" />
          </div>
          <div>
            <p class="text-[13px] font-bold text-gray-900">Sigurno plaćanje</p>
            <p class="text-[12px] text-gray-600 leading-snug mt-0.5">SSL šifrovano · 3D Secure</p>
          </div>
        </div>
      </div>

      <!-- Logoi kurira + payment providera (placeholder text-cards do zamene SVG-ovima) -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">Post Express</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">BEX</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">AKS</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">D-Express</span>

          <span class="hidden sm:block w-px h-5 bg-gray-300 mx-2" aria-hidden="true" />

          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">VISA</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">Mastercard</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">DinaCard</span>
          <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-300 bg-white">3D Secure</span>
        </div>
        <p class="text-[11px] text-center text-gray-400 mt-3">
          Sve transakcije su zaštićene SSL šifrovanjem i 3D Secure protokolom
        </p>
      </div>
    </div>
  </section>
</template>
