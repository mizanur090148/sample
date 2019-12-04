/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue'
import { Form, HasError, AlertError } from 'vform'
import VueProgressBar from 'vue-progressbar'
import Toasted from 'vue-toasted'
/*
import VueProgressBar from 'vue-progressbar' */
import Snotify, { SnotifyPosition } from 'vue-snotify'

window.Form = Form
const Snotifyoptions = {
  toast: {
    position: SnotifyPosition.rightTop
  }
}

const VueProgressBarOptions = {
  color: '#50d38a',
  failedColor: '#874b4b',
  thickness: '5px',
  transition: {
    speed: '0.2s',
    opacity: '0.6s',
    termination: 300
  },
  autoRevert: true,
  location: 'top',
  inverse: false
}

Vue.use(VueProgressBar, VueProgressBarOptions);
Vue.use(Snotify, Snotifyoptions);
Vue.use(Toasted);


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('buyer-component', require('./components/BuyerComponent.vue').default);
Vue.component('color-component', require('./components/ColorComponent.vue').default);
Vue.component('size-component', require('./components/SizeComponent.vue').default);
Vue.component('user-component', require('./components/UserComponent.vue').default);
Vue.component('pagination', require('./components/partial/PaginationComponent.vue').default);

Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
