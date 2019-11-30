<template>
  <div class="sv-form-wrapper flex flex-col px-6 mb-12">
    <sv-form-group label="Identifier" class="mr-6 mb-6">
      <sv-text-field
          v-model="resource.identifier"
      ></sv-text-field>
    </sv-form-group>

    <sv-form-group label="Label" class="mr-6 mb-6">
      <sv-text-field
          v-model="resource.label"
      ></sv-text-field>
    </sv-form-group>

    <sv-form-group label="Addon" class="mr-6 mb-6">
      <sv-select-field
          v-model="resource.addon" :options="model.addons"
      ></sv-select-field>
    </sv-form-group>

    <sv-form-group label="Navigation" class="mr-6 mb-6">
      <sv-text-field
          v-model="resource.nav"
      ></sv-text-field>
    </sv-form-group>

    <sv-form-group label="Connection" class="mr-6 mb-6">
      <sv-text-field
          v-model="resource.connection"
      ></sv-text-field>
    </sv-form-group>
  </div>
</template>

<script>
export default {
  name: 'ResourceConfig',
  props: ['resource', 'model']
}
</script>