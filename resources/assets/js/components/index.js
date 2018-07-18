import Vue from 'vue'
import Card from './Card'
import Child from './Child'
import Button from './Button'
import Checkbox from './Checkbox'
import vSelect from 'vue-select'
import { HasError, AlertError, AlertSuccess } from 'vform'
import VueSweetalert2 from 'vue-sweetalert2'
import JsonExcel from 'vue-json-excel'
import Toasted from 'vue-toasted';
import VueCurrencyFilter from 'vue-currency-filter'
import money from 'v-money'
import Datepicker from 'vuejs-datepicker';

// Components that are registered globaly.
[
  Card,
  Child,
  Button,
  Checkbox,
  HasError,
  AlertError,
  AlertSuccess
].forEach(Component => {
  Vue.component(Component.name, Component)
})

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
    require('bootstrap-datepicker');
    require('admin-lte');
} catch (e) {}

Vue.component('v-select', vSelect)
Vue.use(VueSweetalert2)
Vue.use(Toasted)
Vue.component('Datepicker',Datepicker)
Vue.component('downloadExcel', JsonExcel)
Vue.use(VueCurrencyFilter,
{
  symbol : '',
  thousandsSeparator: ',',
  fractionCount: 0,
  fractionSeparator: '',
  symbolPosition: 'front',
  symbolSpacing: false
})
Vue.use(money,{
  decimal: '.',
  thousands: ',',
  prefix: '',
  suffix: '',
  precision: 0,
  masked: false
})