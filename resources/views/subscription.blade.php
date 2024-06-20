@extends('layouts.layout')

@section('content')

    <div class="container">
        <div class="row subcription-row mt-5">

            @php
                $currentPlan = app('subscription_helper')->getCurrentSubscription();
            @endphp

            @foreach ($plans as $plan)
                <div class="col-sm-4">
                    <div class="card subcription-card">
                        <h2>{{ $plan->name }}
                            @if($currentPlan && $currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                                (Active)
                            @endif
                        </h2>
                        <h4>${{ $plan->amount }} Charge</h4>
                        @if($currentPlan && $currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                            @if($currentPlan->plan_interval == 'lifetime')
                                <button class="btn btn-primary">Subscribed</button>
                            @else
                                <button class="btn btn-danger subscriptionCancel" data-id="{{ $currentPlan->stripe_subscription_id }}">Cancel</button>
                            @endif
                        @else
                            <button class="btn btn-primary confirmationBtn @if($currentPlan &&  $currentPlan->plan_interval == 'lifetime') disabled-btn @endif" data-id="{{ $plan->id }}" data-toggle="modal" data-target="#confirmationModal">Subscribe</button>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalTitle">...</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="confirmation-data">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary continueBuyPlan">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe Card Modal -->
    <div class="modal fade" id="stripeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stripeModalTitle">Buy Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="planId" id="planId">
                    <div id="card-element"></div>
                    <div id="card-errors" style="color: red;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="buyPlanSubmitBtn" class="btn btn-primary">Buy Plan</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script src="https://js.stripe.com/v3/"></script>

<script>
    $(document).ready(function() {
        $('.confirmationBtn').click(function() {
            $('#confirmationModalTitle').text('...');
            $('.confirmation-data').html('<i class="fa fa-spinner fa-spin"></i>');
            var planId = $(this).data('id');
            $('#planId').val(planId);

            $.ajax({
                type: "POST",
                url: "{{ route('getPlanDetails') }}",
                data: { id: planId, _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var html = '';
                        $('#confirmationModalTitle').text(data.name + ' ($' + data.amount + ')');
                        html += `<p>` + response.msg + `</p>`;
                        $('.confirmation-data').html(html);
                    } else {
                        alert('Something went wrong!');
                    }
                }
            });
        });

        $('.continueBuyPlan').click(function() {
            $('#confirmationModal').modal('hide');
            $('#stripeModal').modal('show');
        });

        $('.subscriptionCancel').click(function() {
            var obj = $(this);
            var stripeSubscriptionId = $(this).data('id');
            $(obj).html('Please Wait <i class="fa fa-spinner fa-spin" style="font-size:24px !important;"></i>');
            $(obj).attr('disabled', 'disabled');

            $.ajax({
                url: "{{ route('cancelSubscription') }}",
                type: "POST",
                data: { stripe_subscription_id: stripeSubscriptionId, _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        alert(response.msg);
                        window.location.reload();
                    } else {
                        alert("Something went wrong!");
                        $(obj).html('Cancel');
                        $(obj).removeAttr('disabled');
                    }
                }
            });
        });

        // Stripe code start
        if (window.Stripe) {
            var stripe = Stripe("{{env('STRIPE_PUBLIC_KEY') }}");
            var elements = stripe.elements();
            var card = elements.create('card', { hidePostalCode: true });
            card.mount('#card-element');

            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            var submitButton = document.getElementById('buyPlanSubmitBtn');
            submitButton.addEventListener('click', function(ev) {
                submitButton.innerHTML = 'Please Wait <i class="fa fa-spinner fa-spin" style="font-size:24px !important;"></i>';
                submitButton.setAttribute("disabled", "disabled");

                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        submitButton.innerHTML = 'Buy Plan';
                        submitButton.removeAttribute("disabled");
                    } else {
                        createSubscription(result.token);
                    }
                });
            });
        }

        function createSubscription(token) {
            var plan_id = $('#planId').val();
            $.ajax({
                url: "{{ route('createSubscription') }}",
                type: "POST",
                data: { plan_id, data: token, _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        alert(response.msg);
                        window.location.reload();
                    } else {
                        alert("Something went wrong!");
                        $('#buyPlanSubmitBtn').html('Buy Plan');
                        $('#buyPlanSubmitBtn').removeAttr('disabled');
                    }
                }
            });
        }
    });
</script>

@endpush
