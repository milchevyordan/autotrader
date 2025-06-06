<div class="container font-8and82">
    {{ __('Please confirm this Transport Order at your earliest convenience, including a specification of the planned pickup and delivery dates.') }}
</div>

<div class="container font-8and82 break-inside-avoid">
    <div class="w-full break-inside-avoid">
        <div>{{ __('Transport Company`s signature') }}:</div>
        <div class="pt-half">{{ $transportOrder->transportCompany?->name }}</div>

        <div style="padding-left: 350px">
            <img src="{{ public_path($signatureImage) }}" alt="signature image" class="payment-image">
        </div>

        <div>{{ __('Name of signatory') }}:</div>
        <div class="pt-half">{{ __('Position') }}:</div>
        <div class="pt-half">{{ __('Date') }}:</div>
    </div>
</div>

<div class="container font-6and86 break-inside-avoid">
    <div class="fw-bold pb-1 font-8and82">
        {{ __('Transport Terms and Conditions') }}
    </div>

    <div>
        {{ __('The transport takes place between the specified pickup and delivery locations. The respective locations must be informed in accordance with the remarks in the form about the planned pickup and delivery times, but always at least 24 hours in advance.') }}
    </div>

    <div>
        {{ __('The Transport Company inspects the vehicles at both pickup and delivery and records both transfers with signed documents. The driver identifies themselves with a valid ID and verifies the identity of the contact person. Additionally, the driver provides the signed Authorisation for Pickup when collecting the vehicles.') }}
    </div>

    <div>
        {{ __('The Transport Company completes and signs the waybill (CMR) correctly, with details matching the assignment. A copy of the waybill is sent to the client after transport and kept for at least two years.') }}
    </div>

    <div>
        {{ __('The Transport Company is fully responsible for the vehicles during transport, including protection against damage, loss, and theft. Before departure, the Transport Company inspects the vehicles and records any existing damage on the waybill. New damage is reported immediately and fully compensated if caused by negligence.') }}
    </div>

    <div>
        {{ __('The Transport Company`s insurance covers damage, loss, or theft of the vehicles. A copy of the policy is provided upon request. The Transport Company indemnifies the client against claims arising from transport-related damage or loss.') }}
    </div>

    <div>
        {{ __('The transport price includes all additional costs unless otherwise agreed. Payment is made within 30 days of receiving a correct invoice. In case of cancellation, only reasonable costs for already performed work are reimbursed.') }}
    </div>

    <div>
        {{ __('The transport is carried out with trucks in good technical condition. Overloading and actions causing damage are not permitted. During stops, a secure parking area is used, and the vehicles are secured against theft and vandalism.') }}
    </div>

    <div>
        {{ __('The Transport Company strictly adheres to driving and rest time regulations. Violations and any resulting fines are the responsibility of the Transport Company. In cases of force majeure, such as extreme weather conditions, the Transport Company informs the client immediately and seeks an appropriate solution.') }}
    </div>

    <div>
        {{ __('Disputes are preferably resolved through mutual agreement. If this is not possible, the competent court in If this is not possible, the competent court in `s- Hertogenbosch, the Netherlands has jurisdiction.') }}
    </div>

    <div>
        {{ __('By executing this Transport Order, the Transport Company explicitly agrees to all terms and conditions set forth in this document, including liability, adherence to regulations, and compliance with agreed timelines and procedures.') }}
    </div>
</div>
