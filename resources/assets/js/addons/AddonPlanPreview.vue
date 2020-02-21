<template>
    <div v-if="plan" class="card card-light-border panel-price">
        <div class="card-header">
            <div class="media">
                <div class="media-body">
                    <small class="text-muted font-weight-normal">{{ addon.name }}</small>
                    <h4 class="plan-name">{{ plan.name }}</h4>
                </div>
                <div class="media-right">
                    <img class="media-object" :src="addon.logo" :alt="addon.name" width="45">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p>{{ plan.attributes.description }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <ul class="plan-features">
                        <li v-for="feature in plan.features"><i class="fa fa-check"></i> {{ feature }}</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-12">
                    <div class="plan-price d-flex w-100 justify-content-between">
                        <h4>{{ plan.price | currency }}  <small>/ {{ plan.usageType == "licensed" ? plan.interval : plan.unit }}</small></h4>
                        <h5 v-if="plan.trialDays > 0" class="pull-right"><small><b>Free {{ plan.trialDays }} Day Trial</b></small></h5>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mt-3">
                    <button v-if="spark.userId" @click="openProvisionerModal" class="btn btn-success btn-lg w-100">Install</button>
                    <button v-else @click="openLogin" class="btn btn-success btn-lg w-100">Login to Install</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['addon', 'teams', 'currentTeam'],
        /**
         * The component's data.
         */
        data: function () {
            return {
                plan: null,
            };
        },
        /**
         * Prepare the component.
         */
        mounted: function() {
            var self = this;
            // Listen for a plan being selected
            Bus.$on('addonPlanSelected', function (plan) {
                self.plan = plan;
            });
        },
        methods: {
            /**
             * Open the provision add-on modal
             */
            openProvisionerModal: function() {
                Bus.$emit('addonPlanInstall', this.addon, this.plan);
            },
            /**
             * Open the login
             */
            openLogin: function() {
                window.location = '/login';
            }

        }
    }
</script>
