<template>
    <div class="list-group price-list mb-2">
        <a v-for="plan in plans"  href="#" v-on:click.prevent="selectPlan(plan)" class="list-group-item list-group-item-action flex-column align-items-start" :class="{'active': selectedPlan == plan}">
            <div class="d-flex w-100 justify-content-between">
                <div class="col-9">
                    <h5 class="mb-1">{{ plan.name }}</h5>
                    <p class="mb-1">{{ plan.attributes.description }}</p>
                </div>
                <div class="col-3 plan align-self-center text-right">
                    <h5 class="price mb-0">{{ plan.price | currency }}</h5>
                    <small class="schedule">/&nbsp;{{ plan.usageType == "licensed" ? plan.interval : plan.unit }}</small>
                </div>
            </div>
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
                axios.get(`/${_.get(Spark, 'spark-addons.routes.api', 'api')}/addons/${this.addon.id}/plans`).then(response => {
                    this.plans = response.data;
                    this.selectPlan(_.find(response.data, { default: true }));
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
