<script setup lang="ts">
const props = defineProps<{ error: { statusCode: number; message: string } }>()

const is404 = computed(() => props.error.statusCode === 404)
const is500Plus = computed(() => props.error.statusCode >= 500)

useHead({
  title: is404.value ? 'Stranica nije pronađena — sloj kolektiv' : 'Greška — sloj kolektiv',
})

function handleError() {
  clearError({ redirect: '/' })
}

const popularLinks = [
  { to: '/proizvodi', label: 'Svi proizvodi', icon: 'lucide:layout-grid' },
  { to: '/kategorije', label: 'Kategorije', icon: 'lucide:folder-tree' },
  { to: '/pretraga', label: 'Pretraga', icon: 'lucide:search' },
  { to: '/kontakt', label: 'Kontakt', icon: 'lucide:mail' },
]
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col">
    <!-- Header -->
    <header class="border-b border-gray-200 py-4">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
        <NuxtLink to="/"><UiAtomsBrandMark size="sm" /></NuxtLink>
        <NuxtLink to="/" class="text-[13px] text-gray-500 hover:text-primary-700 transition-colors">
          ← Početna
        </NuxtLink>
      </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
      <div class="max-w-2xl w-full">
        <!-- 404 -->
        <template v-if="is404">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            <!-- Branded SVG illustration -->
            <div class="order-2 md:order-1">
              <div class="aspect-square bg-primary-50 relative overflow-hidden">
                <!-- Dot pattern background -->
                <div class="absolute inset-0 bg-placeholder opacity-60" />
                <!-- Big 404 -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-[140px] md:text-[180px] font-bold text-primary-700/15 leading-none tracking-tighter select-none">
                    404
                  </span>
                </div>
                <!-- Foreground icon -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-24 h-24 bg-white border-2 border-primary-700 flex items-center justify-center text-primary-700 shadow-md">
                    <Icon name="lucide:map-pin-off" class="w-10 h-10" :stroke-width="1.5" />
                  </div>
                </div>
                <!-- Accent corner -->
                <div class="absolute -top-2 -right-2 w-10 h-10 bg-accent-500" aria-hidden="true" />
              </div>
            </div>

            <!-- Copy -->
            <div class="order-1 md:order-2">
              <p class="text-[12px] uppercase tracking-[0.2em] text-accent-700 font-semibold mb-3">Greška 404</p>
              <h1 class="text-[32px] md:text-[40px] font-bold text-gray-900 leading-[1.05] tracking-tight">
                Ovo mesto<br>ne postoji.
              </h1>
              <p class="text-[15px] text-gray-600 mt-4 leading-relaxed">
                Stranica koju tražite je verovatno premeštena ili nikada nije ni postojala. Predlažemo da krenete od početne ili pretražite naše proizvode.
              </p>

              <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <UiAtomsButton size="lg" @click="handleError">
                  Nazad na početnu
                </UiAtomsButton>
                <NuxtLink to="/proizvodi">
                  <UiAtomsButton variant="outline" size="lg" class="w-full">
                    Pogledaj proizvode
                  </UiAtomsButton>
                </NuxtLink>
              </div>
            </div>
          </div>

          <!-- Popular links -->
          <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold mb-4">Možda vas zanima</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <NuxtLink
                v-for="link in popularLinks"
                :key="link.to"
                :to="link.to"
                class="flex items-center gap-2.5 px-4 py-3 border border-gray-200 hover:border-primary-400 hover:bg-primary-50/50 transition-colors group"
              >
                <Icon :name="link.icon" class="w-4 h-4 text-gray-500 group-hover:text-primary-700 transition-colors" />
                <span class="text-[13px] font-medium text-gray-700 group-hover:text-primary-700">{{ link.label }}</span>
              </NuxtLink>
            </div>
          </div>
        </template>

        <!-- 500 / generic -->
        <template v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="order-2 md:order-1">
              <div class="aspect-square bg-warm/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-placeholder opacity-50" />
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-[140px] md:text-[180px] font-bold text-gray-900/10 leading-none tracking-tighter select-none">
                    {{ error.statusCode }}
                  </span>
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-24 h-24 bg-white border-2 border-gray-900 flex items-center justify-center text-gray-900 shadow-md">
                    <Icon name="lucide:alert-triangle" class="w-10 h-10" :stroke-width="1.5" />
                  </div>
                </div>
                <div class="absolute -top-2 -right-2 w-10 h-10 bg-danger" aria-hidden="true" />
              </div>
            </div>

            <div class="order-1 md:order-2">
              <p class="text-[12px] uppercase tracking-[0.2em] text-danger font-semibold mb-3">
                Greška {{ error.statusCode }}
              </p>
              <h1 class="text-[32px] md:text-[40px] font-bold text-gray-900 leading-[1.05] tracking-tight">
                Nešto je<br>pošlo naopako.
              </h1>
              <p class="text-[15px] text-gray-600 mt-4 leading-relaxed">
                {{ is500Plus ? 'Problem je na našoj strani — radimo na rešavanju. Pokušajte ponovo za nekoliko trenutaka.' : 'Pokušajte ponovo. Ako se problem nastavi, javite nam.' }}
              </p>

              <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <UiAtomsButton size="lg" @click="handleError">
                  Nazad na početnu
                </UiAtomsButton>
                <a href="mailto:info@elokal.rs">
                  <UiAtomsButton variant="outline" size="lg" class="w-full">
                    Prijavi grešku
                  </UiAtomsButton>
                </a>
              </div>

              <p class="text-[12px] text-gray-400 mt-4">
                Kontakt: <a href="mailto:info@elokal.rs" class="text-primary-700 hover:underline">info@elokal.rs</a>
              </p>
            </div>
          </div>
        </template>
      </div>
    </main>

    <!-- Footer hint -->
    <footer class="border-t border-gray-200 py-6 text-center">
      <p class="text-[11px] text-ink-400 lowercase">© 2026 sloj kolektiv · slojevit nameštaj, dosledno</p>
    </footer>
  </div>
</template>
