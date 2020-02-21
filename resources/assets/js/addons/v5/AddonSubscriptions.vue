<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
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
            <div class="panel-body table-responsive">
                <table class="table table-borderless m-b-none">
                    <thead></thead>
                    <tbody>
                    <tr v-for="plan in addonPlans">
                        <!-- Plan Name -->
                        <td>
                            <div class="btn-table-align" @click="showPlanDetails(plan.addonPlan)">
                                <span style="cursor: pointer;"><strong>{{ plan.addon.name }} {{ plan.addonPlan.name }}</strong> ({{ plan.name }})</span>
                            </div>
                        </td>
                        <!-- Plan Features Button -->
                        <td>
                            <button class="btn btn-default m-l-sm" @click="showPlanDetails(plan.addonPlan)">
                                <i class="fa fa-btn fa-star-o"></i>Plan Features
                            </button>
                        </td>
                        <!-- Plan Price -->
                        <td>
                            <div class="btn-table-align">
                                <span v-if="plan.addonPlan.price == 0">
                                    Free
                                </span>
                                <span v-else>
                                    {{ priceWithTax(plan.addonPlan) | currency }}
                                    /
                                    {{ plan.addonPlan.interval == "monthly" ? "month" : "year" }}
                                </span>
                            </div>
                        </td>
                        <!-- Plan Select Button -->
                        <td class="text-right">
                            <button class="btn btn-danger-outline" @click="confirmCancellation(plan)">Cancel</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Confirm Cancellation Modal -->
        <div class="modal fade" id="modal-confirm-addon-cancellation" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" v-if="cancelling">
                    <div class="modal-header">
                        <button type="button " class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">
                            Cancel Subscription
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="well">
                            <strong>@{{ cancelling.addon.name }} @{{ cancelling.addonPlan.name }}</strong> (@{{ cancelling.name }})</span>
                            &#8212;
                            <span v-if="cancelling.addonPlan.price == 0">Free</span>
                            <span v-else>
                            @{{ priceWithTax(cancelling.addonPlan) | currency }}
                                /
                            @{{ cancelling.addonPlan.interval == "monthly" ? "month" : "year" }}
                        </span>
                        </div>
                        <p>Are you sure you want to cancel your add-on subscription?</p>
                        <p>If you choose to cancel the add-on, all of the add-on's data will be <b>permanently deleted</b>.</p>
                    </div>
                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click="cancel" :disabled="form.busy">
                            <span v-if="form.busy">
                                <i class="fa fa-btn fa-spinner fa-spin"></i>Cancelling
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
        mixins: [require('settings/subscription/update-subscription')],
        data: function() {
            return {
                form: new SparkForm({}),
                cancelling: null,
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
                axios.get('/api/' + this.team.slug + '/addons/subscriptions')
                        .then(response => {
                    this.addonPlans = response.data;
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
                Spark.delete('/api/' + this.team.slug + '/addons/' + this.cancelling.id + '/cancel', this.form)
                        .then(() => {
                    this.getPlans();
                $('#modal-confirm-addon-cancellation').modal('hide');
            });
            }
        }
    }
</script>
