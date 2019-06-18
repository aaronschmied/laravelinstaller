<template>
    <div class="FormErrorContainer" v-if="hasErrors">
        <transition-group name="error-list" tag="div">
            <div class="uk-alert-danger" uk-alert v-for="(messages, bag) in errors" :key="messages[0]">
                <a class="uk-alert-close" uk-close></a>
                <p v-for="message in messages" v-text="message"></p>
            </div>
        </transition-group>
    </div>
</template>

<script>
  export default {
    name: "FormErrors",
    props: [
      'errors'
    ],
    computed: {
      hasErrors () {
        return !! this.errors
      },
    },
    methods: {
      hasErrorsFor(field) {
        return !! this.errorsFor(field)
      },
      errorsFor(field) {
        return this.errors[field] || [];
      }
    }
  }
</script>
