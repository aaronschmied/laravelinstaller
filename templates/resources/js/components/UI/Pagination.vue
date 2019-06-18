<template>
    <div class="Pagination uk-flex uk-flex-middle">
        <button :class="'Pagination__Button ' + buttonClass"
                :disabled="!canOpenPrevPage || disabled"
                v-on:click="prevPage">
            <i uk-icon="chevron-left"></i>
        </button>
        <span :class="'Pagination__Label ' + labelClass"
              v-text="`Seite ${currentPage} von ${lastPage}`">
        </span>
        <button :class="'Pagination__Button ' + buttonClass"
                :disabled="!canOpenNextPage || disabled"
                v-on:click="nextPage">
            <i uk-icon="chevron-right"></i>
        </button>
    </div>
</template>

<script>
  export default {
    name: "Pagination",
    props: {
      currentPage: Number,
      lastPage: Number,
      disabled: Boolean,
      buttonClass: {
        type: String,
        default: 'uk-button uk-button-small uk-button-default',
      },
      labelClass: {
        type: String,
        default: 'uk-margin-small-left uk-margin-small-right',
      }
    },
    methods: {
      prevPage () {
        if (this.canOpenPrevPage) {
          this.$emit('prevPage')
        }
      },
      nextPage () {
        if (this.canOpenNextPage) {
          this.$emit('nextPage');
        }
      },
    },
    computed: {
      canOpenPrevPage () {
        return (this.currentPage > 1)
      },
      canOpenNextPage () {
        return (this.currentPage < this.lastPage)
      }
    }
  }
</script>
