import store from '~/store'

export default (to, from, next) => {
  if (store.getters['auth/user']['user_role_s_i'][0].id_role !== 3 ) {
    next({ name: 'not-found' })
  } else {
    next()
  }
}
