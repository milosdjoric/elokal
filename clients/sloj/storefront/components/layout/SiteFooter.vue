<script setup lang="ts">
const email = ref('')
const subscribed = ref(false)

function subscribe() {
  if (!email.value) return
  subscribed.value = true
  email.value = ''
}

const productLinks = [
  { to: '/proizvodi', label: 'Svi proizvodi' },
  { to: '/proizvodi?sort=created_at', label: 'Novo u kolekciji' },
  { to: '/proizvodi?featured=1', label: 'Naš izbor' },
  { to: '/poklon-kartica', label: 'Poklon kartice' },
  { to: '/blog', label: 'Magazin' },
]

const helpLinks = [
  { to: '/cesta-pitanja', label: 'Česta pitanja' },
  { to: '/kontakt', label: 'Kontakt' },
  { to: '/uslovi-koriscenja#reklamacije', label: 'Reklamacije i povraćaj' },
  { to: '/pracenje', label: 'Praćenje porudžbine' },
  { to: '/nalog', label: 'Moj nalog' },
]

const aboutLinks = [
  { to: '/o-nama', label: 'O nama' },
  { to: '/prodavnice', label: 'Pronađi nas' },
  { to: '/uslovi-koriscenja', label: 'Uslovi korišćenja' },
  { to: '#', label: 'Politika privatnosti' },
  { to: '#', label: 'Politika kolačića' },
]
</script>

<template>
  <footer class="bg-ink-900 text-ink-200">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 pt-16 pb-10">
      <!-- Top: brand + newsletter -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 pb-12 border-b border-white/10">
        <div class="lg:col-span-5">
          <UiAtomsBrandMark size="md" inverse />
          <p class="mt-5 text-[14px] leading-relaxed text-ink-300 max-w-md">
            Šperploča je slojevita. Brend, sajt i proizvod sve to reflektuju.
            Dizajni preuzeti iz Opendesk kataloga (CC license).
          </p>
          <div class="mt-6 flex items-center gap-4">
            <a href="#" aria-label="Instagram" class="text-ink-300 hover:text-terra-300 transition-colors">
              <Icon name="lucide:instagram" class="w-5 h-5" />
            </a>
            <a href="#" aria-label="Facebook" class="text-ink-300 hover:text-terra-300 transition-colors">
              <Icon name="lucide:facebook" class="w-5 h-5" />
            </a>
            <a href="#" aria-label="Pinterest" class="text-ink-300 hover:text-terra-300 transition-colors">
              <Icon name="lucide:music-2" class="w-5 h-5" />
            </a>
          </div>
        </div>

        <div class="lg:col-span-7 lg:pl-10">
          <h3 class="text-[16px] font-medium text-ply-50">Mesečni email sa novim dizajnima</h3>
          <p class="mt-1 text-[13px] text-ink-400">Bez spam-a. Otkažite jednim klikom.</p>
          <form class="mt-4 flex gap-0 border-b border-white/20 focus-within:border-terra-300 transition-colors" @submit.prevent="subscribe">
            <input
              v-model="email"
              type="email"
              required
              placeholder="vasa@email.rs"
              class="flex-1 py-3 text-[14px] bg-transparent text-ply-50 placeholder:text-ink-400 outline-none"
            />
            <button
              type="submit"
              class="px-2 py-3 text-[13px] text-ink-200 hover:text-terra-300 transition-colors whitespace-nowrap flex items-center gap-2"
            >
              <span v-if="!subscribed">Prijavi se →</span>
              <span v-else class="flex items-center gap-1.5 text-terra-300">
                <Icon name="lucide:check" class="w-4 h-4" /> Hvala
              </span>
            </button>
          </form>
        </div>
      </div>

      <!-- Link columns -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 py-12">
        <div>
          <h4 class="text-[12px] font-medium text-ply-50 uppercase tracking-[0.12em] mb-4">Proizvodi</h4>
          <ul class="space-y-2.5">
            <li v-for="link in productLinks" :key="link.label">
              <NuxtLink :to="link.to" class="text-[14px] text-ink-300 hover:text-terra-300 transition-colors">
                {{ link.label }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-[12px] font-medium text-ply-50 uppercase tracking-[0.12em] mb-4">Pomoć</h4>
          <ul class="space-y-2.5">
            <li v-for="link in helpLinks" :key="link.label">
              <NuxtLink :to="link.to" class="text-[14px] text-ink-300 hover:text-terra-300 transition-colors">
                {{ link.label }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-[12px] font-medium text-ply-50 uppercase tracking-[0.12em] mb-4">Kompanija</h4>
          <ul class="space-y-2.5">
            <li v-for="link in aboutLinks" :key="link.label">
              <NuxtLink :to="link.to" class="text-[14px] text-ink-300 hover:text-terra-300 transition-colors">
                {{ link.label }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-[12px] font-medium text-ply-50 uppercase tracking-[0.12em] mb-4">Kontakt</h4>
          <ul class="space-y-2.5 text-[14px]">
            <li>
              <a href="tel:+381111234567" class="text-ink-300 hover:text-terra-300 transition-colors tabular-nums">+381 11 123 4567</a>
            </li>
            <li>
              <a href="mailto:info@slojkolektiv.rs" class="text-ink-300 hover:text-terra-300 transition-colors">info@slojkolektiv.rs</a>
            </li>
            <li class="text-ink-400">Beograd · Srbija</li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="pt-6 border-t border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-[12px] text-ink-400">
        <p>© {{ new Date().getFullYear() }} sloj kolektiv · Sva prava zadržana</p>
        <p class="tabular-nums">PIB: 123456789 · MB: 12345678</p>
      </div>
    </div>
  </footer>
</template>
