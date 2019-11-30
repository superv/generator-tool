import components from './app/components'
import routes from './app/routes'

export default {
  install(Vue, { superv }) {

    superv.router().addRoutes(routes)
    superv.registerComponents(components)
  },
}
