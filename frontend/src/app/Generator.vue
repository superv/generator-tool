<template>
  <div class="sv-page  flex-grow text-copy-secondary">
    <sv-page-header title="Resource Generator">
      <template slot="actions" v-if="model.analyzed">
        <sv-button  @click="model.clear()" color="danger">Clear</sv-button>
        <sv-button  @click="model.save()" color="primary">Save</sv-button>
        <sv-button  @click="model.write()" color="blue">Generate</sv-button>
<!--        <sv-button @click="model.addResource()" color="primary">Add Resource</sv-button>-->
      </template>
    </sv-page-header>

    <div class="sv-page-body flex flex-col   p-0 sm:p-2 sm:pb-4 md:p-4 md:pb-12">

      <generator-config v-if="!model.analyzed" :model="model"></generator-config>


      <div v-if="model.analyzed" v-for="resource in model.resources" class="sv-card w-full">
        <resource :resource="resource" :model="model"></resource>
      </div>

    </div>
  </div>
</template>

<script>
import { SvPageHeader } from '@superv/ui'
import GeneratorModel from './GeneratorModel'
import FieldConfig from './FieldConfig'
import RelationConfig from './RelationConfig'
import ResourceConfig from './ResourceConfig'
import ResourceRelations from './ResourceRelations'
import ResourceFields from './ResourceFields'
import Resource from './Resource'
import GeneratorConfig from './GeneratorConfig'

export default {
  name: 'Generator',
  components: {
    GeneratorConfig,
    Resource,
    ResourceFields,
    ResourceRelations,
    ResourceConfig,
    RelationConfig,
    FieldConfig,
    SvPageHeader
  },
  data() {
    return {
      model: null,
      meta: {
        title: 'Generator',
      },
    }
  },

  created() {
    this.model = new GeneratorModel()

    const resources = this.$storage.get('generator.resources')
    if (resources) {
      this.model.setResources(resources)
      this.model.analyzed = true
      this.model.ready = true
    }
    // this.model.normalize()
  },
  methods: {},
}
</script>
