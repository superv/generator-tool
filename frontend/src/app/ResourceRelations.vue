<template>
    <div class="px-6 mb-12">
<!--      <sv-button @click="model.addRelation(resource)" icon="plus"></sv-button>-->
      <table border="0" cellpadding="0" cellspacing="0" class="table slim">
        <thead>
          <tr>
            <th style="max-width: 180px;">Name</th>
            <th>Related Resource</th>
            <th>Relation Type</th>
            <th width="60"></th>
          </tr>
        </thead>
        <tbody>
          <tr :key="relation.name" v-for="relation in resource.relations">
            <td><sv-text-field v-model="relation.name"></sv-text-field></td>
            <td><sv-select-field v-model="relation.related" :options="model.allResources"></sv-select-field></td>
            <td><sv-select-field v-model="relation.type" :options="model.relationTypes"></sv-select-field></td>
            <td>
              <relation-config :type="relation.type" v-model="relation.config"></relation-config>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</template>

<script>
import RelationConfig from './RelationConfig'
export default {
  name: 'ResourceRelations',
  components: { RelationConfig },
  props: ['resource', 'model']
}
</script>
