import store from '~/store'

export default (to, from, next) => {
  if (store.getters['auth/check']) {
	  if (store.getters['auth/user']['user_role_s_i'][0].id_role == 8){
		next({ name: 'transaksi' })  
	  }else{
		  next({ name: 'dashboard' })  
	  }
		
  } else {
    next({ name: 'login' })
  }
}
