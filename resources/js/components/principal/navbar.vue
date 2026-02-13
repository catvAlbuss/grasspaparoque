<script setup>
import { ref } from 'vue'

const menuOpen = ref(false)

const links = [
  { label: 'Inicio', to: { path: '/', hash: '#home' } },
  { label: 'Servicios', to: { path: '/', hash: '#services' } },
  { label: 'Contacto', to: { path: '/', hash: '#contact' } },
  { label: 'Dashboard', to: { path: '/dashboard' } },
]
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/90 backdrop-blur">
    <nav class="m-auto flex max-w-full items-center justify-between px-2 py-3 sm:px-6">
      <a href="#home" class="text-xl font-semibold tracking-tight text-slate-900">
        Grass Papa Roque
      </a>

      <div class="hidden items-center gap-2 md:flex">
        <RouterLink v-for="link in links" :key="link.label" :to="link.to"
          class="text-sm font-medium text-slate-700 transition hover:text-slate-900">
          {{ link.label }}
        </RouterLink>
      </div>

      <RouterLink to="/login"
        class="hidden rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 md:inline-flex">
        Login
      </RouterLink>

      <button type="button"
        class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-700 transition hover:bg-slate-100 md:hidden"
        @click="menuOpen = !menuOpen" :aria-expanded="menuOpen ? 'true' : 'false'" aria-label="Abrir menú">
        <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
          aria-hidden="true">
          <path fill-rule="evenodd"
            d="M3 5h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2zm0 4h14a1 1 0 010 2H3a1 1 0 010-2z"
            clip-rule="evenodd" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
          aria-hidden="true">
          <path fill-rule="evenodd"
            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
            clip-rule="evenodd" />
        </svg>
      </button>
    </nav>

    <div class="md:hidden" :class="menuOpen ? 'block' : 'hidden'">
      <div class="border-t border-slate-200 bg-white px-4 py-4 sm:px-6">
        <div class="flex flex-col gap-3">
          <RouterLink v-for="link in links" :key="link.label" :to="link.to"
            class="text-sm font-medium text-slate-700 transition hover:text-slate-900" @click="menuOpen = false">
            {{ link.label }}
          </RouterLink>
          <RouterLink to="/login"
            class="rounded-full bg-slate-900 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-slate-800"
            @click="menuOpen = false">
            Login
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- <div
        class="flex min-h-screen flex-col items-center bg-white-50 p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">

                <Link v-if="$page.props.auth.user" :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                        Register
                    </Link>
                </template>
            </nav>
        </header>
    </div> -->

  </header>
</template>
