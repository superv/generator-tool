<template>
  <div>
    <div class="sv-card-header">
      <div class="sv-card-header-label">
        <sv-boolean-field v-model="resource.enabled"></sv-boolean-field>
          <sv-badge label="Table" :color="resource.enabled ? 'blue' : 'gray'">{{ resource.table }}</sv-badge>
        <sv-badge label="Resource" :color="resource.enabled ? 'blue' : 'gray'">{{ resource.identifier }}</sv-badge>
<!--        <sv-badge label="Addon" color="blue">{{ resource.addon }}</sv-badge>-->
<!--        <header class="sv-card-header-title"> {{ resource.identifier }} <small></small>-->
<!--        </header>-->
      </div>
      <div class="sv-card-header-toolbar flex items-center " v-if="resource.enabled">
        <ul class="flex items-center border-b-2 border-background-secondary p-5 pb-0">
          <li v-for="tab in tabs"
              :class="[tab.active ? 'border-primary': 'border-transparent' ]"
              class="flex justify-between items-center  -mb-2px  mx-2  border-b-2">
            <a @click="showTab(tab)" class="font-semibold block  mr-1 flex-grow pb-4 pr-0  pl-2">
              <span class="text-sm text-gray-500 font-semibold uppercase tracking-widest">{{ tab.label }}</span>
            </a>
          </li>

        </ul>
      </div>
    </div>
    <div class="sv-card-body" v-if="activeTab.key">
      <div class="bg-background-primary flex flex-col flex-grow">
        <div class="pt-6">
          <component :is="activeTab.component"
                     :model="model"
                     :resource="resource"
          >

          </component>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import ResourceConfig from './ResourceConfig'
import ResourceRelations from './ResourceRelations'
import ResourceFields from './ResourceFields'

export default {
  name: 'Resource',
  components: { ResourceFields, ResourceRelations, ResourceConfig },
  props: ['resource', 'model'],
  data() {
    return {
      current: null,
      tabs: [
        { key: 'config', label: 'Config', component: 'resource-config' },
        { key: 'relations', label: 'Relations', component: 'resource-relations' },
        { key: 'fields', label: 'Fields', component: 'resource-fields' },
      ]
    }
  },
  created() {
    this.tabs.map(tab => this.$set(tab, 'active', false))
  },
  computed: {
    activeTab() {
      const tab = this.tabs.find(tab => tab.active)

      if (!tab) {
        return {}
        // this.tabs[0].active = true
        // return this.tabs[0]
      }
      return tab
    }
  },

  methods: {
    showTab(tab) {

      if (tab.key === this.activeTab.key) {
        return tab.active = false
      }
      this.tabs.map(_tab => _tab.active = false)

      tab.active = true
    },
  },
}
</script>
