<template>
  <div>
    <button @click.prevent="show" class="sv-btn blue sm">
      Config
    </button>
    <sv-modal v-if="showModal" :visible.sync="showModal" @close="close">
      <template slot="body" slot-scope="{close}">
        <div class="p-4">
          <div class="sv-form-group-wrapper">
            <sv-form-group v-for="field in fields" :label="field.label" class="mb-2">
              <sv-text-field
                  v-model="config$[field.name]"
              ></sv-text-field>
            </sv-form-group>

          </div>

          <div class="text-right p-4">
            <sv-button @click="save" color="green">Save</sv-button>
          </div>
        </div>
      </template>
    </sv-modal>
  </div>
</template>

<script>
export default {
  name: 'RelationConfig',
  props: ['value', 'type'],
  data() {
    return {
      showModal: false,
      fields$: [
        { name: 'foreign_key', label: 'Foreign Key'},
        { name: 'local_key', label: 'Local Key'},
        { name: 'owner_key', label: 'Owner Key'},
        { name: 'morph_name', label: 'Morph Name'},
        { name: 'related', label: 'Pivot Resource'},
        { name: 'pivot_table', label: 'Pivot Table'},
        { name: 'pivot_foreign_key', label: 'Pivot Foreign Key'},
        { name: 'pivot_related_key', label: 'Pivot Related Key'},
      ],
      fieldsTypeMap: {
        has_one: ['foreign_key', 'local_key'],
        morph_one: ['foreign_key', 'local_key'],
        belongs_to: ['foreign_key', 'owner_key'],
        morph_to: ['morph_name', 'owner_key'],
        has_many: ['foreign_key', 'local_key'],
        belongs_to_many: ['pivot_table', 'pivot_foreign_key', 'pivot_related_key'],
        morph_to_many: ['related', 'pivot_related_key','morph_name'],
        morph_many: ['morph_name'],
      },
      config$: {}
    }
  },
  computed: {
    fields() {
      return this.fields$.filter(field => this.fieldsTypeMap[this.type].indexOf(field.name) > -1)
    }
  },
  methods: {
    show() {
      this.showModal = true
      this.config$ = {...this.value}
    },
    save() {
      this.$emit('input', this.config$)
      this.showModal = false
    },
    close() {
      this.config$ = {}
    }
  },
}
</script>
