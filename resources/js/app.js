/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vuetify from '../plugins/vuetify'
import router from './router'
import msal from 'vue-msal'
import store from './store'

import VueSignature from 'vue-signature'

import { extend, ValidationProvider, ValidationObserver, setInteractionMode, localize} from "vee-validate";
import {required, integer, min, max, required_if, email, alpha,
    alpha_spaces, double, length, between} from "vee-validate/dist/rules";
import es from 'vee-validate/dist/locale/es.json'

setInteractionMode("eager");
extend("required", {
    ...required,
});
extend("min", {
    ...min,
});

extend("email", {
    ...email,
});

extend("max", {
    ...max,
});

extend("integer", {
    ...integer,
});

extend("required_if", {
    ...required_if,
});

extend("alpha", {
    ...alpha,
});
extend("alpha_spaces", {
    ...alpha_spaces,
});

extend("double", {
    ...double,
});

extend("length", {
    ...length,
});

extend("between", {
    ...between,
});

localize('es')
localize({
    es
})

Vue.use(msal, {
    auth: {
        clientId: 'fc93eeed-724e-4321-80f2-c6c40511f040',
        authority: 'https://login.microsoftonline.com/27dd8c19-004a-4a66-a2e7-12c6420ca639/',
        redirectUri: process.env.MIX_APP_URL + '/home#/',
        requireAuthOnInitialize :true,
    },
    request: {
        scopes: ["openid", "profile" ,"User.Read" ,"User.ReadWrite" ,"email"]
    },
    graph: {
        callAfterInit: true,
        endpoints: {
            photo: {
                url: '/me/photo/$value',
                responseType: 'blob',
                force: true
            },
        }
    },
    framework: {
        globalMixin: true,
    }
})


Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('app-container', require('./components/appContainer.vue').default);
Vue.component('validation-observer', ValidationObserver)
Vue.component('validation-provider', ValidationProvider)
Vue.component('vue-signature', VueSignature)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    vuetify: Vuetify,
    store,
    router,
    el: '#app',
});