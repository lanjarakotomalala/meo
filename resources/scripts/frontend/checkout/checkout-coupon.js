export class CheckoutCoupon {
    constructor() {
        this.nativeCouponInput = document.getElementById('coupon_code');
        this.fakeCouponInput = document.getElementById('coupon_code_checkout');
        this.nativeSubmitCoupon = document.querySelector('.form-row.form-row-last button[type="submit"][name="apply_coupon"]');
        this.fakeSubmitCoupon = document.getElementById('save_coupon_code');
        this.couponWrapper = this.fakeCouponInput ? this.fakeCouponInput.closest('.coupon_checkout') : null;
    }

    initialize() {
        if (this.nativeCouponInput && this.fakeCouponInput && this.nativeSubmitCoupon && this.fakeSubmitCoupon && this.couponWrapper) {
            this.addEventListeners();
            this.relocateCouponError();
        }
    }

    addEventListeners() {
        this.fakeCouponInput.addEventListener('input', () => {
            this.nativeCouponInput.value = this.fakeCouponInput.value;
        });

        this.fakeSubmitCoupon.addEventListener('click', () => {
            this.nativeSubmitCoupon.click();
        });
    }

    relocateCouponError() {
        const jq = window.jQuery;

        if (!jq) {
            return;
        }


        jq(document.body).on('applied_coupon_in_checkout', () => {
            this.couponWrapper.querySelectorAll('.coupon-error-notice').forEach((notice) => notice.remove());

            const notice = document.getElementById('coupon-error-notice');

            if (notice) {
                this.couponWrapper.appendChild(notice);
            }
        });
    }
}

const checkoutCoupon = new CheckoutCoupon();
checkoutCoupon.initialize();