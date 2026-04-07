<template>
  <div class="flex min-h-screen bg-gray-50">
    <LayoutSidebar />

    <div class="flex-1 flex flex-col">
      <LayoutHeader />

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
