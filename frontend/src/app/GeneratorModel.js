import { superv } from '@superv/ui'
import Vue from 'vue'
export default class GeneratorModel {
  $fieldTypes = [
    { type: 'boolean', label: 'Boolean' },
    { type: 'checkbox', label: 'Checkbox' },
    { type: 'datetime', label: 'Datetime', maps: ['date', 'datetime', 'timestamp'] },
    { type: 'email', label: 'Email' },
    { type: 'encrypted', label: 'Encrypted' },
    { type: 'number', label: 'Number', maps: ['int', 'tinyint', 'smallint', 'mediumint'] },
    { type: 'select', label: 'Select' },
    { type: 'text', label: 'Text', maps: ['varchar'] },
    { type: 'textarea', label: 'Textarea' },
  ]

  $relationTypes = [
    { type: 'has_one', label: 'Has One' },
    { type: 'morph_one', label: 'Morph One' },
    { type: 'belongs_to', label: 'Belongs To' },
    { type: 'morph_to', label: 'Morph To' },
    { type: 'has_many', label: 'Has Many' },
    { type: 'morph_many', label: 'Morph Many' },
    { type: 'belongs_to_many', label: 'Belongs To Many' },
    { type: 'morph_to_many', label: 'Morph To Many' },
  ]

  $resources = []
  $availableResources = []

  $addons = []

  $config = {
    models_path: 'app/Koel',
    connection: '',
  }
  analyzed = false

  constructor() {
    // this.fetch()
  }

  async analyze() {
    const resp = await superv.api.post('sv/tools/generator/analyze', this.$config)
    this.$addons = resp.data.addons
    this.$availableResources = resp.data.available_resources
    this.$resources = resp.data.resources

    this.normalize()
    this.analyzed = true
  }

  save() {
    superv.storage().set('generator.resources', this.$resources)
  }

  clear() {
    superv.storage().forget('generator.resources')
    this.analyzed = false
  }

  async write() {

    // this.normalize()

    const post = { resources: this.$resources }
    const resp = await superv.api.post('sv/tools/generator/write', post)

    if (!resp.ok) {
      return superv.notify.error(resp)
    }

    superv.notify.success('Done!')
  }

  get config() {
    return this.$config
  }

  get fieldTypes() {
    return this.$fieldTypes.map(fieldType => {
      return { text: fieldType.label, value: fieldType.type }
    })
  }

  get relationTypes() {
    return this.$relationTypes.map(relationType => {
      return { text: relationType.label, value: relationType.type }
    })
  }

  get resources() {
    return this.$resources
  }

  get addons() {
    return this.$addons
  }

  get allResources() {

    return [...this.$availableResources, ...this.$resources.map(resource => resource.identifier)]
  }

  setResources(resources) {
    this.$resources = resources
  }

  addResource() {
    this.$resources = [{ ...resourceStub }, ...this.$resources]
  }

  addRelation(resource) {
    resource.relations = [{ ...relationStub }, ...resource.relations]
  }

  addField(resource) {
    resource.fields = [{ ...fieldStub }, ...resource.fields]
  }

  normalize() {
    this.$resources.map(resource => {
      Vue.prototype.$set(resource, 'enabled', true)
      Vue.prototype.$set(resource, 'connection', this.$config.connection)
      Object.values(resource.relations).map(relation => {
      })
    })
  }

  getResourceFromTable(table) {
    return this.$resources.find(resource => this.normalizeTableName(resource.table) === this.normalizeTableName(table))
  }

  normalizeTableName(table) {
    if (this.$config.table_prefix) {
      return table.replace(this.$config.table_prefix, '')
    }

    return table
  }
}

const resourceStub = {
  enabled: true,
  addon: null,
  identifier: 'addon.resource',
  label: 'New Resource',
  nav: 'acp.resources',
  fields: [],
  relations: [],
  table: 'resource_table_name'
}

const relationStub = { name: 'relation_name', type: null, related: null, config: {} }

const fieldStub = { label: 'Field Label', name: 'field_name', column_type: null, type: null, config: {} }