<template>
    <div class="px-6 mb-12">
<!--      <sv-button @click="model.addField(resource)" icon="plus"></sv-button>-->
      <table border="0" cellpadding="0" cellspacing="0" class="table slim">
        <thead>
          <tr>
            <th style="max-width: 180px;">Label</th>
            <th>Name</th>
            <th>Field Type</th>
            <th width="60"></th>
          </tr>
        </thead>
        <tbody>
          <tr :key="field.name" v-for="field in resource.fields">
            <td><sv-text-field v-model="field.label"></sv-text-field></td>
            <td><sv-text-field v-model="field.name"></sv-text-field></td>
            <td><sv-select-field v-model="field.type" :options="model.fieldTypes"></sv-select-field></td>
            <td>
              <field-config v-model="field.config"></field-config>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</template>

<script>
import FieldConfig from './FieldConfig'
export default {
  name: 'ResourceFields',
  components: { FieldConfig },
  props: ['resource', 'model']
}
</script>
