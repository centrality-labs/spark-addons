<template>
    <div>
        <!-- Active Addon-Subscriptions -->
        <div class="card card-default">
            <div class="card-header">
                <div class="pull-left" :class="{'btn-table-align': hasMonthlyAndYearlyPlans}">
                    Add-on Subscriptions
                </div>
                <!-- Interval Selector Button Group -->
                <div class="pull-right">
                    <div class="btn-group" v-if="hasMonthlyAndYearlyPlans">
                        <!-- Monthly Plans -->
                        <button type="button" class="btn btn-default"
                                @click="showMonthlyPlans"
                                :class="{'active': showingMonthlyPlans}">
                            Monthly
                        </button>
                        <!-- Yearly Plans -->
                        <button type="button" class="btn btn-default"
                                @click="showYearlyPlans"
                                :class="{'active': showingYearlyPlans}">
                            Yearly
                        </button>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="table-responsive">
                <table class="table table-responsive-sm table-valign-middle mb-0 ">
                    <thead></thead>
                    <tbody>
                    <tr v-for="plan in addonPlans" v-bind:key="plan.id">
                        <!-- Plan Name -->
                        <td>
                            <div class="btn-table-align" @click="showPlanDetails(plan.addonPlan)">
                                <span style="cursor: pointer;">
                                    <strong>{{ plan.addon.name }} {{ plan.addonPlan.name }}</strong> &#8212; #{{ plan.id }}
                                </span>
                            </div>
                        </td>
                        <!-- Status -->
                        <td>
                            <span v-if="isActiveSubscription(plan)" class="badge badge-success"><i class="fa fa-check"></i> Active</span>
                        </td>
                        <!-- Plan Features Button -->
                        <td>
                            <button class="btn btn-default m-l-sm" @click="showPlanDetails(plan.addonPlan)">
                                <i class="fa fa-btn fa-star-o"></i> Features
                            </button>
                        </td>
                        <!-- Plan Price -->
                        <td>
                            <div class="btn-table-align">
                                <span v-if="plan.addonPlan.price == 0">
                                    Free
                                </span>
                                <span v-else>
                                    <strong class="table-plan-price">{{ priceWithTax(plan.addonPlan) | currency }}</strong>
                                    /
                                    {{ plan.addonPlan.usageType == "licensed" ? plan.addonPlan.interval : plan.addonPlan.unit }}
                                </span>
                            </div>
                        </td>
                        <!-- Usage (for metered only) -->
                        <td>
                            <span v-if="plan.addonPlan.usageType == 'metered'">
                                {{ plan.current_usage }} {{ plan.addonPlan.unit | pluralize(plan.current_usage) }}
                            </span>
                        </td>
                        <!-- Plan Select Button -->
                        <td class="text-right">
                            <button v-if="isActiveSubscription(plan)" class="btn btn-danger" @click="confirmCancellation(plan)">Cancel</button>
                            <button v-if="isSubscriptionIsOnGracePeriod(plan)" type="button" class="btn btn-warning" @click="resume(plan)" :disabled="form.busy">
                                <span v-if="form.busy && resuming.id == plan.id">
                                    <i class="fa fa-btn fa-spinner fa-spin"></i> Resuming
                                </span>
                                <span v-else>
                                    Resume
                                </span>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="loadingAddonPlans" class="card-body">Loading add-on subscriptions...</div>
            <div v-if="addonPlans.length == 0 && !loadingAddonPlans" class="card-body">You currently have no active add-on subscriptions.</div>
        </div>
        <!-- Confirm Cancellation Modal -->
        <div class="modal fade" id="modal-confirm-addon-cancellation" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" v-if="cancelling">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Subscription</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <strong>{{ cancelling.addon.name }} {{ cancelling.addonPlan.name }}</strong> ({{ cancelling.name || "#" + cancelling.id }})</span>
                                &#8212;
                                <span v-if="cancelling.addonPlan.price == 0">Free</span>
                                <span v-else>
                                    {{ priceWithTax(cancelling.addonPlan) | currency }}
                                        /
                                    {{ cancelling.addonPlan.usageType == "licensed" ? cancelling.addonPlan.interval : cancelling.addonPlan.unit }}
                                </span>
                            </div>
                        </div>
                        <p>Are you sure you want to cancel your add-on subscription?</p>
                        <p>If you choose to cancel the add-on, all of the add-on's data will be <b>permanently deleted</b>.</p>
                    </div>
                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-outline-danger" @click="cancel" :disabled="form.busy">
                            <span v-if="form.busy">
                                <i class="fa fa-btn fa-spinner fa-spin"></i> Cancelling
                            </span>
                            <span v-else>
                                Yes, Cancel
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        mixins: [
            require('settings/subscription/update-subscription')
        ],
        data: function() {
            return {
                form: new SparkForm({}),
                resuming: {},
                cancelling: null,
                loadingAddonPlans: false,
                addonPlans: []
            }
        },
        /**
         * Prepare the component.
         */
        mounted: function() {
            this.getPlans();
        },
        methods: {
            /**
             * Get the active plans for the application.
             */
            getPlans: function() {
                if(Spark.usesTeams && !Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/settings/${Spark.teamsPrefix}/${this.team.id}/addons/subscriptions`;
                } else if(Spark.usesTeams && Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/${this.team.slug}/addons/subscriptions`;
                } else {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/addons/subscriptions`;
                }
                this.loadingAddonPlans = true;
                axios.get(url).then(response => {
                    this.addonPlans = _.orderBy(response.data, ['ends_at', 'created_at'], ['desc', 'desc']);
                    this.loadingAddonPlans = false;
                });
            },
            /**
             * Confirm the cancellation operation.
             */
            confirmCancellation(plan) {
                this.cancelling = plan;
                $('#modal-confirm-addon-cancellation').modal('show');
            },
            /**
             * Cancel the current subscription.
             */
            cancel() {
                if(Spark.usesTeams && !Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/settings/${Spark.teamsPrefix}/${this.team.id}/addons/${this.cancelling.id}/cancel`;
                } else if(Spark.usesTeams && Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/${this.team.slug}/addons/${this.cancelling.id}/cancel`;
                } else {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/addons/${this.cancelling.id}/cancel`;
                }
                Spark.delete(url, this.form)
                        .catch(errors => {
                            if (errors.response.status == 422) {
                                this.planForm.errors.set(errors.response.data.errors);
                            } else {
                                this.planForm.errors.set({plan: [__("We were unable to update your subscription. Please contact customer support.")]});
                            }
                        })
                        .then(() => {
                            this.getPlans();
                            $('#modal-confirm-addon-cancellation').modal('hide');
                        })
            },
            resume(plan) {
                if(Spark.usesTeams && !Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/settings/${Spark.teamsPrefix}/${this.team.id}/addons/${plan.id}/subscribe`;
                } else if(Spark.usesTeams && Spark.teamsIdentifiedByPath) {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/${this.team.slug}/addons/${plan.id}/subscribe`;
                } else {
                    var url = `/${_.get(Spark, 'spark-addons.routes.api', 'api')}/addons/${plan.id}/subscribe`;
                }
                this.resuming = plan;
                Spark.put(url, this.form)
                        .then(() => {
                            this.getPlans();
                        })
                        .catch(errors => {
                            if (errors.response.status == 422) {
                                this.planForm.errors.set(errors.response.data.errors);
                            } else {
                                this.planForm.errors.set({plan: [__("We were unable to update your subscription. Please contact customer support.")]});
                            }
                        })
                        .finally(() => {
                            this.resuming = {};
                        });
            },
            /**
             * Determine if the current subscription is active.
             */
            isSubscriptionIsOnGracePeriod(subscription) {
                return subscription &&
                        subscription.ends_at &&
                        moment.utc().isBefore(moment.utc(subscription.ends_at));
            },
            isActiveSubscription(subscription) {
                return subscription && ! subscription.ends_at;
            },
        }
    }
</script>
