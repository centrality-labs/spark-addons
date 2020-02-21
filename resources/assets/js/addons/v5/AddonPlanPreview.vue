<template>
    <div v-if="plan" class="panel panel-default panel-price">
        <div class="panel-body">
            <div class="media">
                <div class="media-body">
                    <small class="text-muted font-weight-normal">{{ addon.name }}</small>
                    <h4 class="plan-name">{{ plan.name }}</h4>
                </div>
                <div class="media-right">
                    <img class="media-object" :src="addon.logo" alt="SocketCluster" width="45">
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
            <div class="row m-t-xs">
                <div class="col-md-12">
                    <div class="plan-price">
                        <h4 class="price">
                            <span class="pull-left">${{ plan.price }}  <small>/ {{ plan.interval == "monthly" ? "month" : "year" }}</small></span>
                            <span v-if="plan.trialDays > 0" class="pull-right"><small><b>Free {{ plan.trialDays }} Day Trial</b></small></span>
                            <div class="clearfix"></div>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 m-t-md">
                    <button v-if="spark.userId" @click="openProvisionerModal" class="btn btn-success btn-lg col-xs-12">Install</button>
                    <a v-else href="/login" class="btn btn-success btn-lg col-xs-12">Login to Install</a>
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
        }
    }
</script>
