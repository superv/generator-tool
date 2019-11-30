<template>
  <div class="sv-form-wrapper flex flex-col px-6 mb-12">
    <sv-form-group label="Models Location" class="mr-6 mb-6">
      <sv-text-field
          v-model="config.models_path"
      ></sv-text-field>
    </sv-form-group>


    <sv-form-group label="DB Connection" hint="Leave blank for default" class="mr-6 mb-6">
      <sv-text-field
          v-model="config.connection"
      ></sv-text-field>
    </sv-form-group>



    <div class="text-right">
      <sv-button @click="model.analyze()" color="blue">Analyze</sv-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GeneratorConfig',
  props: ['model'],
  computed: {
    config() {
      return this.model.config
    }
  },
}
</script>
