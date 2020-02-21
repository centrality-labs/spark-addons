Vue.component('addon-plans', require('./addons/AddonPlans.vue'));
Vue.component('addon-plan-preview', require('./addons/AddonPlanPreview.vue'));
Vue.component('addon-provisioner', require('./addons/AddonProvisioner.vue'));
Vue.component('addon-subscriptions', require('./addons/AddonSubscriptions.vue'));
/**
 * Pluralize a string
 */
Vue.filter('pluralize', (word, amount) => {
    return amount > 1 ? `${word}s` : word
})
