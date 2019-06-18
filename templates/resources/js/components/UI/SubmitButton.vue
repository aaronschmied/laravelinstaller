<template>
    <button type="submit" class="SubmitButton uk-button uk-button-default">
        <span class="SubmitButton__label" :style="labelStyles">
            <slot>Submit</slot>
        </span>
        <span uk-spinner="ratio:.8" class="SubmitButton__spinner" v-if="isSubmitting"></span>
    </button>
</template>

<script>
  export default {
    name: "SubmitButton",
    props: {
      isSubmitting: Boolean,
    },
    computed: {
      labelStyles () {
        return {
          opacity: this.isSubmitting ? 0 : 1,
        }
      }
    }
  }
</script>
