<template>
    <div class="list-group price-list m-b-sm">
        <a v-for="plan in plans"  href="#" v-on:click.prevent="selectPlan(plan)" class="list-group-item" :class="{'active': selectedPlan == plan}">
            <div class="col-xs-9">
                <h4 class="list-group-item-heading">{{ plan.name }}</h4>
                <p class="list-group-item-text">{{ plan.attributes.description }}</p>
            </div>
            <div class="col-xs-3 plan">
                <div class="price">${{ plan.price }}</div>
                <div class="schedule">/&nbsp;{{ plan.interval == "monthly" ? "month" : "year" }}</div>
            </div>
            <div class="clearfix"></div>
        </a>
    </div>
</template>
<script>
    export default {
        props: ['addon'],
        /**
         * The component's data.
         */
        data: function () {
            return {
                selectedPlan: null,
                plans: []
            };
        },
        /**
         * Prepare the component.
         */
        mounted: function() {
            this.getPlans();
        },
        methods: {
            /**
             * Get the active plans for the add-on.
             */
            getPlans: function () {
                axios.get('/api/addons/' + this.addon.id + '/plans')
                        .then(response => {
                    this.plans = response.data;
                this.selectPlan(_.first(_.where(response.data, { default: true })));
            });
            },
            /**
             * Select an add-on plan
             */
            selectPlan: function(plan) {
                // Change the plan
                this.selectedPlan = plan;
                // Notify other components of the selected plan
                Bus.$emit('addonPlanSelected', plan);
            }
        }
    }
</script>
