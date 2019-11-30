<template>
<!--  <sv-modal-form-->
<!--      title="Config"-->
<!--      :fields="fields">-->
<!--  </sv-modal-form>-->
  <sv-modal v-if="showModal" :visible.sync="showModal" @close="complete">
    <template slot="body" slot-scope="{close}">
      <sv-form
          :title="title"
          :fields="fields"
          :url="url"
      ></sv-form>
    </template>
  </sv-modal>
</template>

<script>
export default {
  name: 'FieldConfig',
  data() {
    return {
      showModal: false,
      fields: []
    }
  },
}
</script>

<style scoped>

</style>