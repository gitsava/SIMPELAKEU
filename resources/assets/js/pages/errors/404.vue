<template>
  <card class="text-center">
    <h3 class="mb-4">{{ $t('page_not_found') }}</h3>

    <div class="links">
      <router-link :to="{ name: 'transaksi' }">
        {{ $t('go_home') }}
      </router-link>
    </div>
  </card>
</template>

<script>
export default {
  name: 'NotFound',
  layout: 'basic'
}
</script>
