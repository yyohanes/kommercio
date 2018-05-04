@section('paypal_external_checkout_summary')
        <script type="text/javascript">
            function addPaypalNote() {
                var checkoutBtn = document.getElementById('place-order-btn');

                if (checkoutBtn) {
                    var redirectText = document.createElement('p');
                    redirectText.innerText = '{{ trans(LanguageHelper::getTranslationKey('order.payment_method.paypal.redirect_to_paypal')) }}';

                    checkoutBtn.parentElement.insertBefore(redirectText, checkoutBtn);
                }
            }

            document.addEventListener('DOMContentLoaded', function(event) {
                addPaypalNote();
            });

            addPaypalNote();
        </script>
@show
