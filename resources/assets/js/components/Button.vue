<template>
  <button :type="nativeType" v-on:click="onClick" :disabled="loading" :class="{
    [`btn-${type}`]: true,
    'btn-block': block,
    'btn-lg': large,
    'btn-loading': loading
  }" class="btn">
    <slot/>
  </button>
</template>

<script>
export default {
  name: 'VButton',

  props: {
    method: {
      type: Function,
      default : null
    },
    type: {
      type: String,
      default: 'primary'
    },

    nativeType: {
      type: String,
      default: 'submit'
    },

    loading: {
      type: Boolean,
      default: false
    },

    block: {
      type: Boolean,
      default: false
    },

    large: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    onClick: function(){
      this.method()
    }    
  }
}
</script>
